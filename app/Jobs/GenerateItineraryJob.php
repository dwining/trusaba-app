<?php

namespace App\Jobs;

use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Services\OpenCodeClient;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateItineraryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(public int $itineraryId) {}

    public function handle(OpenCodeClient $openCode): void
    {
        $itinerary = Itinerary::with('user.travellerProfile')->findOrFail($this->itineraryId);

        $itinerary->update(['status' => 'processing']);

        $user = $itinerary->user;
        $profile = $user->travellerProfile;

        $age = $profile?->birth_date ? Carbon::parse($profile->birth_date)->age : 25;

        $hobbies = $profile?->hobbies ?? [];
        $interests = $profile?->interests ?? [];

        // Convert arrays to strings for prompt
        $hobbyStr = implode(', ', $hobbies ?: ['umum']);
        $interestStr = implode(', ', $interests ?: ['umum']);

        $systemPrompt = config('opencode.itinerary_system_prompt');
        $jsonSchema = json_encode(config('opencode.itinerary_json_schema'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $userPrompt = <<<PROMPT
Buatkan itinerary perjalanan wisata dengan detail berikut:

TUJUAN: {$itinerary->destination}
TANGGAL: {$itinerary->start_date->format('Y-m-d')} sampai {$itinerary->end_date->format('Y-m-d')} ({$itinerary->duration_days} hari)
PESERTA: {$itinerary->total_participants} orang
BUDGET TOTAL: Rp {$itinerary->budget_input}
USIA TRAVELLER: {$age} tahun
HOBI: {$hobbyStr}
MINAT: {$interestStr}

Sertakan rekomendasi untuk setiap hari:
- Hotel / penginapan (dengan estimasi harga per malam)
- Tempat wisata (dengan estimasi harga tiket dan waktu kunjungan)
- Restoran untuk sarapan, makan siang, dan makan malam
- Transportasi lokal
- Rekomendasi oleh-oleh (di hari terakhir)

Estimasikan biaya setiap item secara realistis sesuai budget.
Buat jadwal yang mempertimbangkan jarak antar lokasi dan waktu tempuh.

Kembalikan dalam format JSON berikut:
{$jsonSchema}
PROMPT;

        // Call OpenCode via the real session-based API (sync flow)
        $rawText = $openCode->generateItinerary($systemPrompt, $userPrompt);
        $response = $openCode->extractJsonFromText($rawText);

        $itinerary->update([
            'ai_raw_response' => $response,
        ]);

        $days = $response['days'] ?? $response['itinerary']['days'] ?? [];

        // Fallback: if OpenCode returned HTML (web UI) instead of structured JSON,
        // generate a mock itinerary for MVP demo purposes
        if (empty($days) || isset($response['raw_response'])) {
            Log::info('GenerateItineraryJob: using mock itinerary fallback', [
                'itinerary_id' => $this->itineraryId,
                'response_type' => isset($response['raw_response']) ? 'html_ui' : 'no_days',
            ]);
            $days = $this->mockItinerary($itinerary);
        }

        $totalBudget = 0;
        $currentDate = Carbon::parse($itinerary->start_date);

        foreach ($days as $dayIndex => $day) {
            $dayNumber = $day['day'] ?? ($dayIndex + 1);
            $dateStr = $day['date'] ?? $currentDate->copy()->addDays($dayNumber - 1)->format('Y-m-d');
            $schedule = $day['schedule'] ?? $day['items'] ?? [];

            $sortOrder = 0;
            foreach ($schedule as $item) {
                $cost = (int) ($item['estimated_cost'] ?? 0);
                $totalBudget += $cost;

                ItineraryItem::create([
                    'itinerary_id' => $itinerary->id,
                    'day_number' => $dayNumber,
                    'schedule_time' => $item['time'] ?? '09:00',
                    'type' => $item['type'] ?? 'other',
                    'name' => $item['name'] ?? '',
                    'description' => $item['description'] ?? null,
                    'location' => $item['location'] ?? null,
                    'estimated_cost' => $cost,
                    'is_bookable' => (bool) ($item['is_bookable'] ?? false),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        $title = $response['title'] ?? $response['itinerary']['title'] ?? "Trip ke {$itinerary->destination}";
        $itinerary->update([
            'title' => $title,
            'estimated_budget' => $totalBudget,
            'status' => 'draft',
        ]);
    }

    /**
     * Generate a mock itinerary for MVP demo when OpenCode API is not available.
     */
    protected function mockItinerary(Itinerary $itinerary): array
    {
        $start = Carbon::parse($itinerary->start_date);
        $dest = $itinerary->destination;

        return match ($itinerary->duration_days) {
            1 => [[
                'day' => 1,
                'date' => $start->format('Y-m-d'),
                'theme' => "Hari di {$dest}",
                'schedule' => [
                    ['time' => '10:00', 'type' => 'attraction', 'name' => "Wisata {$dest}", 'description' => 'Jelajahi tempat wisata utama', 'estimated_cost' => 150000, 'is_bookable' => true, 'duration_minutes' => 180],
                    ['time' => '13:00', 'type' => 'restaurant', 'name' => 'Restoran Lokal', 'description' => 'Makan siang khas daerah', 'estimated_cost' => 100000, 'is_bookable' => false, 'duration_minutes' => 60],
                    ['time' => '17:00', 'type' => 'shopping', 'name' => 'Pasar Oleh-oleh', 'description' => 'Belanja souvenir khas', 'estimated_cost' => 200000, 'is_bookable' => false, 'duration_minutes' => 90],
                ],
            ]],
            2 => [
                [
                    'day' => 1,
                    'date' => $start->format('Y-m-d'),
                    'theme' => "Hari Pertama di {$dest}",
                    'schedule' => [
                        ['time' => '14:00', 'type' => 'hotel', 'name' => "Hotel {$dest} Indah", 'description' => 'Check-in hotel · Standard Room', 'estimated_cost' => 800000, 'is_bookable' => true, 'duration_minutes' => 30],
                        ['time' => '17:00', 'type' => 'attraction', 'name' => "Spot Sunset {$dest}", 'description' => 'Menikmati matahari terbenam', 'estimated_cost' => 50000, 'is_bookable' => true, 'duration_minutes' => 120],
                        ['time' => '19:30', 'type' => 'restaurant', 'name' => 'Restoran Malam', 'description' => 'Makan malam dengan menu spesial', 'estimated_cost' => 150000, 'is_bookable' => false, 'duration_minutes' => 60],
                    ],
                ],
                [
                    'day' => 2,
                    'date' => $start->copy()->addDay()->format('Y-m-d'),
                    'theme' => "Hari Kedua di {$dest}",
                    'schedule' => [
                        ['time' => '08:00', 'type' => 'restaurant', 'name' => 'Sarapan Hotel', 'description' => 'Buffet breakfast', 'estimated_cost' => 0, 'is_bookable' => false, 'duration_minutes' => 45],
                        ['time' => '10:00', 'type' => 'attraction', 'name' => "Wisata Alam {$dest}", 'description' => 'Eksplorasi alam sekitar', 'estimated_cost' => 200000, 'is_bookable' => true, 'duration_minutes' => 240],
                        ['time' => '14:00', 'type' => 'restaurant', 'name' => 'Rumah Makan Tradisional', 'description' => 'Makan siang khas', 'estimated_cost' => 120000, 'is_bookable' => false, 'duration_minutes' => 60],
                        ['time' => '16:00', 'type' => 'shopping', 'name' => 'Pusat Oleh-oleh', 'description' => 'Belanja buah tangan', 'estimated_cost' => 250000, 'is_bookable' => false, 'duration_minutes' => 90],
                    ],
                ],
            ],
            default => $this->mockMultiDay($itinerary, $start),
        };
    }

    protected function mockMultiDay(Itinerary $itinerary, Carbon $start): array
    {
        $days = [];
        $dest = $itinerary->destination;

        for ($d = 1; $d <= $itinerary->duration_days; $d++) {
            $date = $start->copy()->addDays($d - 1);
            $isFirst = $d === 1;
            $isLast = $d === $itinerary->duration_days;

            $schedule = [];

            if ($isFirst) {
                $schedule[] = ['time' => '14:00', 'type' => 'hotel', 'name' => "Hotel {$dest} Indah", 'description' => 'Check-in · Standard Room', 'estimated_cost' => 800000, 'is_bookable' => true, 'duration_minutes' => 30];
            } else {
                $schedule[] = ['time' => '07:30', 'type' => 'restaurant', 'name' => 'Sarapan Hotel', 'description' => 'Buffet breakfast', 'estimated_cost' => 0, 'is_bookable' => false, 'duration_minutes' => 45];
            }

            $schedule[] = ['time' => '09:00', 'type' => 'attraction', 'name' => "Wisata {$dest} Day {$d}", 'description' => 'Destinasi wisata unggulan', 'estimated_cost' => 200000, 'is_bookable' => true, 'duration_minutes' => 180];
            $schedule[] = ['time' => '13:00', 'type' => 'restaurant', 'name' => 'Restoran Lokal', 'description' => 'Makan siang', 'estimated_cost' => 150000, 'is_bookable' => false, 'duration_minutes' => 60];

            if ($isLast) {
                $schedule[] = ['time' => '16:00', 'type' => 'shopping', 'name' => 'Pasar Oleh-oleh', 'description' => 'Belanja souvenir', 'estimated_cost' => 300000, 'is_bookable' => false, 'duration_minutes' => 90];
            } else {
                $schedule[] = ['time' => '15:00', 'type' => 'attraction', 'name' => "Spot Foto {$dest}", 'description' => 'Tempat foto Instagramable', 'estimated_cost' => 100000, 'is_bookable' => true, 'duration_minutes' => 120];
            }

            $schedule[] = ['time' => '19:00', 'type' => 'restaurant', 'name' => 'Makan Malam Spesial', 'description' => 'Menu signature chef lokal', 'estimated_cost' => 180000, 'is_bookable' => false, 'duration_minutes' => 60];

            $days[] = [
                'day' => $d,
                'date' => $date->format('Y-m-d'),
                'theme' => "Hari ke-{$d} di {$dest}",
                'schedule' => $schedule,
            ];
        }

        return $days;
    }

    public function failed(\Throwable $exception): void
    {
        Itinerary::where('id', $this->itineraryId)->update(['status' => 'failed']);
        Log::error('GenerateItineraryJob failed', [
            'itinerary_id' => $this->itineraryId,
            'error' => $exception->getMessage(),
        ]);
    }
}
