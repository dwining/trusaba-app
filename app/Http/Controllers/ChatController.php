<?php

namespace App\Http\Controllers;

use App\Services\OpenCodeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    protected int $inactivityTimeout = 600; // 10 menit
    protected int $idlePromptDelay = 30;    // 30 detik setelah prompt idle

    public function index()
    {
        // Reset activity timer on page load
        $this->touchActivity();

        return view('chat');
    }

    public function send(Request $request, OpenCodeClient $openCode)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
        ]);

        $user = Auth::user();
        $this->touchActivity();

        // Build travel-focused context
        $context = [
            'user_name' => $user->name,
            'travel_status' => 'Sedang merencanakan perjalanan',
        ];

        $profile = $user->travellerProfile;
        if ($profile) {
            $hobbies = $profile->hobbies ?? [];
            $interests = $profile->interests ?? [];

            $context['user_profile'] = [
                'usia' => $profile->birth_date ? $profile->birth_date->age . ' tahun' : 'tidak diketahui',
                'hobi' => $hobbies,
                'minat' => $interests,
                'budget_default' => $profile->default_budget ? 'Rp ' . number_format($profile->default_budget, 0, ',', '.') : 'tidak ditentukan',
            ];
        }

        // Get active or recent itinerary
        $activeItinerary = $user->itineraries()
            ->whereIn('status', ['confirmed', 'ongoing', 'draft'])
            ->latest()
            ->first();

        if ($activeItinerary) {
            $context['active_trip'] = [
                'destination' => $activeItinerary->destination,
                'tanggal' => $activeItinerary->start_date->format('d M') . ' - ' . $activeItinerary->end_date->format('d M Y'),
                'durasi' => $activeItinerary->duration_days . ' hari',
                'peserta' => $activeItinerary->total_participants . ' orang',
                'budget' => $activeItinerary->budget_input ? 'Rp ' . number_format($activeItinerary->budget_input, 0, ',', '.') : 'tidak ditentukan',
                'status' => $activeItinerary->status === 'ongoing' ? 'sedang berjalan' : 'direncanakan',
            ];

            // Include a few itinerary items for reference
            $sampleItems = $activeItinerary->itineraryItems()
                ->select('day_number', 'type', 'name', 'schedule_time')
                ->orderBy('day_number')
                ->orderBy('sort_order')
                ->take(10)
                ->get()
                ->map(fn($i) => "Day {$i->day_number} {$i->schedule_time} — {$i->name} ({$i->type})")
                ->toArray();

            if (!empty($sampleItems)) {
                $context['jadwal_singkat'] = $sampleItems;
            }
        }

        // Chat via OpenCode with stored session
        try {
            $reply = $openCode->chat($validated['message'], $context);
        } catch (\Exception $e) {
            $replies = [
                'Baik, aku catat. Mau tanya soal itinerary, destinasi, atau booking?',
                'Untuk rekomendasi wisata di ' . ($activeItinerary?->destination ?? 'destinasi') . ', aku sarankan cek itinerary-mu dulu ya.',
                'Voucher aktifmu bisa dicek di dashboard Hari Ini. Ada yang bisa dibantu?',
                'Trip-mu masih berjalan lancar. Butuh rekomendasi resto atau tempat wisata?',
            ];
            $reply = $replies[array_rand($replies)];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'reply' => $reply,
                'time' => now()->format('H:i'),
            ]);
        }

        return back()->with('reply', $reply);
    }

    /**
     * Mark user activity timestamp.
     */
    protected function touchActivity(): void
    {
        Cache::put('chat_activity_' . Auth::id(), now()->timestamp, 1800);
    }

    /**
     * Check if user is idle (no activity for 10 minutes).
     */
    public function checkIdle()
    {
        $lastActivity = Cache::get('chat_activity_' . Auth::id());
        $isIdle = !$lastActivity || (now()->timestamp - $lastActivity) > $this->inactivityTimeout;

        return response()->json([
            'idle' => $isIdle,
        ]);
    }

    /**
     * Delete the chat session (called when user is inactive and doesn't respond).
     */
    public function endSession()
    {
        $cacheKey = 'opencode_session_' . Auth::id();
        $sessionId = Cache::get($cacheKey);

        if ($sessionId) {
            try {
                app(OpenCodeClient::class)->deleteSession($sessionId);
            } catch (\Exception $e) {
                // Session might already be expired
            }
            Cache::forget($cacheKey);
        }

        Cache::forget('chat_activity_' . Auth::id());

        return response()->json(['message' => 'Sesi chat diakhiri.']);
    }
}
