<?php

namespace App\Jobs;

use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\Merchant;
use App\Models\MerchantRoom;
use App\Models\MerchantVehicle;
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

        // Query available merchants for this destination + profile
        $availableMerchants = $this->queryAvailableMerchants(
            $itinerary->destination,
            $hobbies,
            $interests,
            $itinerary->start_date,
            $itinerary->end_date,
            $itinerary->duration_days
        );

        $merchantListFormatted = $this->formatMerchantsForPrompt($availableMerchants);

        $userPrompt = <<<PROMPT
Create a travel itinerary with the following details:

DESTINATION: {$itinerary->destination}
DATES: {$itinerary->start_date->format('Y-m-d')} to {$itinerary->end_date->format('Y-m-d')} ({$itinerary->duration_days} days)
PARTICIPANTS: {$itinerary->total_participants} people
TOTAL BUDGET: Rp {$itinerary->budget_input}
TRAVELLER AGE: {$age} years
HOBBIES: {$hobbyStr}
INTERESTS: {$interestStr}

Include recommendations for each day:
- Hotel / accommodation (with estimated price per night)
- Attractions (with estimated ticket prices and visit times)
- Restaurants for breakfast, lunch, and dinner
- Local transportation
- Souvenir recommendations (on the last day)

Estimate each item cost realistically according to budget.
Create a schedule that considers distances between locations and travel time.

Return in the following JSON format:
{$jsonSchema}


AVAILABLE BOOKABLE MERCHANTS — ONLY use these exact names for bookable items:

{$merchantListFormatted}

IMPORTANT RULES:
1. For any item with is_bookable: true, ONLY use a merchant from the list above with the EXACT same name.
2. Do NOT modify, shorten, translate, or approximate merchant names.
3. For non-bookable items (is_bookable: false), you may freely suggest options.
4. If no suitable merchant in the list fits a need, set is_bookable: false — do NOT invent a bookable merchant.
PROMPT;

        // ─── Log: Prompt Data ───────────────────────────────────────
        Log::info('GenerateItineraryJob: sending prompt to AI', [
            'itinerary_id' => $itinerary->id,
            'destination' => $itinerary->destination,
            'duration_days' => $itinerary->duration_days,
            'budget' => $itinerary->budget_input,
            'age' => $age,
            'hobbies' => $hobbyStr,
            'interests' => $interestStr,
            'prompt_length' => strlen($userPrompt),
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'backend' => config('opencode.backend'),
        ]);

        // Call OpenCode via the real session-based API (sync flow)
        $startTime = microtime(true);
        $rawText = $openCode->generateItinerary($systemPrompt, $userPrompt);
        $elapsed = round(microtime(true) - $startTime, 2);
        $response = $openCode->extractJsonFromText($rawText);

        // ─── Log: AI Response ───────────────────────────────────────
        Log::info('GenerateItineraryJob: received AI response', [
            'itinerary_id' => $itinerary->id,
            'elapsed_seconds' => $elapsed,
            'raw_length' => strlen($rawText),
            'raw_preview' => substr($rawText, 0, 500),
            'parsed_keys' => array_keys($response),
            'days_count' => count($response['days'] ?? []),
            'title' => $response['title'] ?? null,
            'estimated_budget' => $response['total_estimated_budget'] ?? $response['estimated_budget'] ?? null,
            'used_fallback' => empty($response['days'] ?? []) || isset($response['raw_response']),
        ]);

        $itinerary->update([
            'ai_raw_response' => $response,
        ]);

        $days = $response['days'] ?? [];

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
                    'description' => $item['description'] ?? $item['tips'] ?? null,
                    'location' => $item['location'] ?? null,
                    'estimated_cost' => $cost,
                    'is_bookable' => (bool) ($item['is_bookable'] ?? false),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        // --- Merchant Matching ------------------------------------
        // Match bookable items to real merchants from the list provided to AI
        $this->matchMerchantsFromList($itinerary, $availableMerchants);
        // ----------------------------------------------------------

        $title = $response['title'] ?? "Trip to {$itinerary->destination}";
        $itinerary->update([
            'title' => $title,
            'estimated_budget' => $totalBudget,
            'status' => 'draft',
        ]);

        // ─── Log: Final Summary ────────────────────────────────────
        $matchedCount = $itinerary->itineraryItems()->whereNotNull('merchant_id')->count();
        Log::info('GenerateItineraryJob: completed', [
            'itinerary_id' => $itinerary->id,
            'title' => $title,
            'total_items' => $itinerary->itineraryItems()->count(),
            'total_estimated_budget' => $totalBudget,
            'user_budget' => $itinerary->budget_input,
            'matched_to_merchant' => $matchedCount,
            'over_budget' => $totalBudget > $itinerary->budget_input,
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
                'theme' => "Day in {$dest}",
                'schedule' => [
                    ['time' => '10:00', 'type' => 'attraction', 'name' => "{$dest} Tour", 'description' => 'Explore main attractions', 'estimated_cost' => 150000, 'is_bookable' => true, 'duration_minutes' => 180],
                    ['time' => '13:00', 'type' => 'restaurant', 'name' => 'Local Restaurant', 'description' => 'Local lunch specialty', 'estimated_cost' => 100000, 'is_bookable' => false, 'duration_minutes' => 60],
                    ['time' => '17:00', 'type' => 'shopping', 'name' => 'Souvenir Market', 'description' => 'Shop for local souvenirs', 'estimated_cost' => 200000, 'is_bookable' => false, 'duration_minutes' => 90],
                ],
            ]],
            2 => [
                [
                    'day' => 1,
                    'date' => $start->format('Y-m-d'),
                    'theme' => "First Day in {$dest}",
                    'schedule' => [
                        ['time' => '14:00', 'type' => 'hotel', 'name' => "{$dest} Hotel", 'description' => 'Check-in hotel · Standard Room', 'estimated_cost' => 800000, 'is_bookable' => true, 'duration_minutes' => 30],
                        ['time' => '17:00', 'type' => 'attraction', 'name' => "{$dest} Sunset Spot", 'description' => 'Enjoying the sunset', 'estimated_cost' => 50000, 'is_bookable' => true, 'duration_minutes' => 120],
                        ['time' => '19:30', 'type' => 'restaurant', 'name' => 'Evening Restaurant', 'description' => 'Dinner with special menu', 'estimated_cost' => 150000, 'is_bookable' => false, 'duration_minutes' => 60],
                    ],
                ],
                [
                    'day' => 2,
                    'date' => $start->copy()->addDay()->format('Y-m-d'),
                    'theme' => "Second Day in {$dest}",
                    'schedule' => [
                        ['time' => '08:00', 'type' => 'restaurant', 'name' => 'Hotel Breakfast', 'description' => 'Buffet breakfast', 'estimated_cost' => 0, 'is_bookable' => false, 'duration_minutes' => 45],
                        ['time' => '10:00', 'type' => 'attraction', 'name' => "{$dest} Nature Tour", 'description' => 'Explore surrounding nature', 'estimated_cost' => 200000, 'is_bookable' => true, 'duration_minutes' => 240],
                        ['time' => '14:00', 'type' => 'restaurant', 'name' => 'Traditional Restaurant', 'description' => 'Local lunch', 'estimated_cost' => 120000, 'is_bookable' => false, 'duration_minutes' => 60],
                        ['time' => '16:00', 'type' => 'shopping', 'name' => 'Souvenir Center', 'description' => 'Shopping for souvenirs', 'estimated_cost' => 250000, 'is_bookable' => false, 'duration_minutes' => 90],
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
                $schedule[] = ['time' => '07:30', 'type' => 'restaurant', 'name' => 'Hotel Breakfast', 'description' => 'Buffet breakfast', 'estimated_cost' => 0, 'is_bookable' => false, 'duration_minutes' => 45];
            }

            $schedule[] = ['time' => '09:00', 'type' => 'attraction', 'name' => "{$dest} Tour Day {$d}", 'description' => 'Top tourist destination', 'estimated_cost' => 200000, 'is_bookable' => true, 'duration_minutes' => 180];
            $schedule[] = ['time' => '13:00', 'type' => 'restaurant', 'name' => 'Local Restaurant', 'description' => 'Lunch', 'estimated_cost' => 150000, 'is_bookable' => false, 'duration_minutes' => 60];

            if ($isLast) {
                $schedule[] = ['time' => '16:00', 'type' => 'shopping', 'name' => 'Souvenir Market', 'description' => 'Souvenir shopping', 'estimated_cost' => 300000, 'is_bookable' => false, 'duration_minutes' => 90];
            } else {
                $schedule[] = ['time' => '15:00', 'type' => 'attraction', 'name' => "{$dest} Photo Spot", 'description' => 'Instagram-worthy photo spot', 'estimated_cost' => 100000, 'is_bookable' => true, 'duration_minutes' => 120];
            }

            $schedule[] = ['time' => '19:00', 'type' => 'restaurant', 'name' => 'Special Dinner', 'description' => 'Local chef signature menu', 'estimated_cost' => 180000, 'is_bookable' => false, 'duration_minutes' => 60];

            $days[] = [
                'day' => $d,
                'date' => $date->format('Y-m-d'),
                'theme' => "Day {$d} in {$dest}",
                'schedule' => $schedule,
            ];
        }

        return $days;
    }

    /**
     * Query available merchants matching destination + traveller profile.
     */
    protected function queryAvailableMerchants(
        string $destination,
        array $hobbies,
        array $interests,
        Carbon|string $startDate,
        Carbon|string $endDate,
        int $durationDays
    ): array {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        // Parse destination for location hints
        $destinationParts = array_map('trim', explode(',', $destination));
        $cityHint = $destinationParts[0] ?? '';
        $provinceHint = $destinationParts[1] ?? '';

        // Query active merchants matching location
        $merchantQuery = Merchant::where('is_active', true);

        if ($cityHint || $provinceHint) {
            $merchantQuery->where(function ($q) use ($cityHint, $provinceHint) {
                if ($cityHint) {
                    $q->where('city', 'LIKE', "%{$cityHint}%")
                        ->orWhere('province', 'LIKE', "%{$cityHint}%")
                        ->orWhere('address', 'LIKE', "%{$cityHint}%");
                }
                if ($provinceHint) {
                    $q->orWhere('city', 'LIKE', "%{$provinceHint}%")
                        ->orWhere('province', 'LIKE', "%{$provinceHint}%")
                        ->orWhere('address', 'LIKE', "%{$provinceHint}%");
                }
            });
        }

        $merchants = $merchantQuery->with('merchantAvailability')->get();

        // Filter by availability in date range
        $merchants = $merchants->filter(function (Merchant $merchant) use ($start, $end) {
            if ($merchant->merchantAvailability->isEmpty()) {
                // No availability data yet — include but deprioritize
                return true;
            }

            return $merchant->merchantAvailability
                ->where('date', '>=', $start->format('Y-m-d'))
                ->where('date', '<=', $end->format('Y-m-d'))
                ->where('available_qty', '>', 0)
                ->isNotEmpty();
        });

        // Score by hobby/interest overlap with merchant profile_tags + name + description
        $profileKeywords = array_merge($hobbies, $interests);
        $profileKeywordsLower = array_map('strtolower', $profileKeywords);

        $scored = $merchants->map(function (Merchant $merchant) use ($profileKeywordsLower) {
            $score = 0;

            // Search text: profile_tags (highest weight) + name + description
            $tags = is_array($merchant->profile_tags) ? $merchant->profile_tags : [];
            $tagText = strtolower(implode(' ', $tags));
            $text = strtolower($tagText.' '.$merchant->name.' '.($merchant->description ?? ''));

            foreach ($profileKeywordsLower as $keyword) {
                if (! $keyword) {
                    continue;
                }
                // Double weight for profile_tags match
                if (str_contains($tagText, $keyword)) {
                    $score += 2;
                } elseif (str_contains($text, $keyword)) {
                    $score++;
                }
            }

            return [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'type' => $merchant->type,
                'city' => $merchant->city,
                'province' => $merchant->province,
                'address' => $merchant->address,
                'description' => $merchant->description ?? '',
                'score' => $score,
            ];
        });

        // Sort by score descending
        $scored = $scored->sortByDesc('score')->values();

        // Group by type, limit per type based on duration
        $typeLimits = [
            'hotel' => min(5, (int) ceil($durationDays * 1.5)),
            'restaurant' => min($durationDays * 3, 20),
            'cafe' => min($durationDays * 3, 20),
            'attraction' => min($durationDays * 2, 15),
            'transport' => min((int) ceil($durationDays * 0.8), 8),
            'other' => min((int) ceil($durationDays * 0.8), 8),
        ];

        $grouped = [];
        foreach ($scored as $merchant) {
            $type = $merchant['type'];
            $limit = $typeLimits[$type] ?? $typeLimits['other'];
            $grouped[$type] ??= [];
            if (count($grouped[$type]) < $limit) {
                $grouped[$type][] = $merchant;
            }
        }

        // Flatten
        $result = [];
        foreach ($grouped as $typeMerchants) {
            foreach ($typeMerchants as $m) {
                $result[] = $m;
            }
        }

        Log::info('GenerateItineraryJob: queried available merchants', [
            'itinerary_id' => $this->itineraryId,
            'destination' => $destination,
            'city_hint' => $cityHint,
            'province_hint' => $provinceHint,
            'total_available' => count($result),
            'by_type' => array_map('count', $grouped),
        ]);

        return $result;
    }

    /**
     * Format merchants array into a readable text block grouped by type.
     */
    protected function formatMerchantsForPrompt(array $merchants): string
    {
        if (empty($merchants)) {
            return '(No available bookable merchants found for this destination. Set is_bookable: false for all items.)';
        }

        // Group by type
        $grouped = [];
        foreach ($merchants as $m) {
            $grouped[$m['type']] ??= [];
            $grouped[$m['type']][] = $m;
        }

        $typeLabels = [
            'hotel' => 'HOTELS',
            'restaurant' => 'RESTAURANTS',
            'cafe' => 'CAFES',
            'attraction' => 'ATTRACTIONS',
            'transport' => 'TRANSPORT',
            'other' => 'SHOPPING & OTHER',
        ];

        $output = '';
        $order = ['hotel', 'restaurant', 'cafe', 'attraction', 'transport', 'other'];

        foreach ($order as $type) {
            if (empty($grouped[$type])) {
                continue;
            }

            $output .= ($typeLabels[$type] ?? strtoupper($type)).":\n";
            foreach ($grouped[$type] as $m) {
                $address = $m['address'] ? " | address: {$m['address']}" : '';
                $desc = $m['description'] ? " | {$m['description']}" : '';
                $output .= "- \"{$m['name']}\"{$address}{$desc}\n";
            }
            $output .= "\n";
        }

        return trim($output);
    }

    /**
     * Match itinerary items back to the real merchants from the provided list.
     * Items that cannot be matched → is_bookable = false, merchant_id = null.
     */
    protected function matchMerchantsFromList(Itinerary $itinerary, array $availableMerchants): void
    {
        $items = $itinerary->itineraryItems()
            ->where('is_bookable', true)
            ->get();

        if ($items->isEmpty()) {
            Log::info('Merchant matching (from list): no bookable items to match', [
                'itinerary_id' => $itinerary->id,
            ]);

            return;
        }

        if (empty($availableMerchants)) {
            Log::info('Merchant matching (from list): no available merchants to match against', [
                'itinerary_id' => $itinerary->id,
            ]);

            return;
        }

        $merchantsByName = [];
        foreach ($availableMerchants as $m) {
            $merchantsByName[strtolower(trim($m['name']))] = $m;
        }

        $matched = 0;
        $unmatched = 0;

        foreach ($items as $item) {
            $itemName = strtolower(trim($item->name));
            $foundMerchant = null;

            // 1. Exact name match (case-insensitive)
            if (isset($merchantsByName[$itemName])) {
                $foundMerchant = $merchantsByName[$itemName];
            }

            // 2. Fuzzy match using similar_text
            if (! $foundMerchant) {
                $bestScore = 0;
                $bestMatch = null;
                foreach ($availableMerchants as $m) {
                    similar_text($itemName, strtolower(trim($m['name'])), $pct);
                    if ($pct > $bestScore) {
                        $bestScore = $pct;
                        $bestMatch = $m;
                    }
                }
                if ($bestScore > 95 && $bestMatch) {
                    $foundMerchant = $bestMatch;
                }
            }

            // 3. Substring match — require minimum 7-char overlap to avoid false positives on short words
            if (! $foundMerchant) {
                foreach ($availableMerchants as $m) {
                    $mName = strtolower(trim($m['name']));
                    // Only match if the overlapping substring is long enough
                    $overlapLen = $this->substringOverlapLen($itemName, $mName);
                    if ($overlapLen >= 12) {
                        $foundMerchant = $m;
                        break;
                    }
                }
            }

            if ($foundMerchant) {
                // Get real merchant base price for this type
                $realCost = $this->getMerchantBasePrice($foundMerchant['id'], $foundMerchant['type'], $item->estimated_cost);

                $item->update([
                    'merchant_id' => $foundMerchant['id'],
                    'name' => $foundMerchant['name'],
                    'location' => $foundMerchant['address'] ?: $item->location,
                    'estimated_cost' => $realCost,
                    'is_bookable' => true,
                ]);
                $matched++;
            } else {
                $item->update([
                    'merchant_id' => null,
                ]);
                $unmatched++;
            }
        }

        Log::info('Merchant matching (from list): completed', [
            'itinerary_id' => $itinerary->id,
            'total_bookable' => $items->count(),
            'matched' => $matched,
            'unmatched' => $unmatched,
            'available_merchants_count' => count($availableMerchants),
        ]);
    }

    /**
     * Get the real base price for a merchant, or fall back to the AI estimate.
     */
    protected function getMerchantBasePrice(int $merchantId, string $type, int $aiEstimate): int
    {
        return match ($type) {
            'hotel' => MerchantRoom::where('merchant_id', $merchantId)
                ->orderBy('price_per_night')
                ->value('price_per_night') ?? $aiEstimate,
            'transport' => MerchantVehicle::where('merchant_id', $merchantId)
                ->orderBy('price_per_day')
                ->value('price_per_day') ?? $aiEstimate,
            default => $aiEstimate,
        };
    }

    /**
     * Match itinerary items to merchants in the database.
     * (Legacy method — kept for reference, matchMerchantsFromList is now used instead.)
     */
    protected function matchMerchants(Itinerary $itinerary): void
    {
        $items = $itinerary->itineraryItems()
            ->where('is_bookable', true)
            ->whereNull('merchant_id')
            ->get();

        if ($items->isEmpty()) {
            Log::info('Merchant matching: no bookable items to match', [
                'itinerary_id' => $itinerary->id,
            ]);

            return;
        }

        $matched = 0;
        foreach ($items as $item) {
            $merchant = $this->findMatchingMerchant($itinerary, $item);
            if ($merchant) {
                $item->update(['merchant_id' => $merchant->id]);
                $matched++;
            }
        }

        Log::info('Merchant matching completed', [
            'itinerary_id' => $itinerary->id,
            'total_bookable' => $items->count(),
            'matched' => $matched,
        ]);
    }

    /**
     * Find a matching merchant for an itinerary item.
     */
    protected function findMatchingMerchant(Itinerary $itinerary, ItineraryItem $item): ?Merchant
    {
        // Map itinerary item type to merchant type
        $merchantType = match ($item->type) {
            'hotel' => 'hotel',
            'restaurant' => ['restaurant', 'cafe'],
            'attraction' => 'attraction',
            'transport' => 'transport',
            'shopping' => 'other',
            default => null,
        };

        if (! $merchantType) {
            return null;
        }

        $query = Merchant::where('is_active', true);

        if (is_array($merchantType)) {
            $query->whereIn('type', $merchantType);
        } else {
            $query->where('type', $merchantType);
        }

        // Try to match by city/province from the item location or itinerary destination
        $location = $item->location ?? $itinerary->destination;
        $locationParts = explode(',', $location);
        $cityHint = trim($locationParts[0] ?? '');

        if ($cityHint) {
            $query->where(function ($q) use ($cityHint) {
                $q->where('city', 'LIKE', "%{$cityHint}%")
                    ->orWhere('province', 'LIKE', "%{$cityHint}%")
                    ->orWhere('address', 'LIKE', "%{$cityHint}%")
                    ->orWhere('description', 'LIKE', "%{$cityHint}%");
            });
        }

        // For hotels: try to match by name similarity
        if ($item->type === 'hotel' && $item->name) {
            $quickHotelQuery = clone $query;
            $query->orWhere('name', 'LIKE', '%'.$item->name.'%');
        }

        $merchant = $query->first();

        // If still no match, get any active merchant of the right type
        if (! $merchant) {
            $merchant = Merchant::where('is_active', true)
                ->when(is_array($merchantType), fn ($q) => $q->whereIn('type', $merchantType))
                ->when(! is_array($merchantType), fn ($q) => $q->where('type', $merchantType))
                ->first();
        }

        return $merchant;
    }

    /**
     * Return the length of the longest common substring between two strings.
     * Used to prevent false-positive matches on short words like "Day" or "Tour".
     */
    protected function substringOverlapLen(string $a, string $b): int
    {
        $maxLen = 0;
        $aLen = strlen($a);
        $bLen = strlen($b);

        for ($i = 0; $i < $aLen; $i++) {
            for ($j = $i + $maxLen; $j <= $aLen; $j++) {
                $sub = substr($a, $i, $j - $i);
                if (str_contains($b, $sub)) {
                    $maxLen = max($maxLen, $j - $i);
                }
            }
        }

        return $maxLen;
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
