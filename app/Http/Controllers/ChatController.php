<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
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

        $hasHistory = ChatMessage::where('user_id', Auth::id())->exists();

        return view('chat.ai', compact('hasHistory'));
    }

    public function send(Request $request, OpenCodeClient $openCode)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'message.required' => 'Message cannot be empty.',
        ]);

        $user = Auth::user();
        $this->touchActivity();

        // Build travel-focused context
        $context = [
            'user_name' => $user->name,
            'travel_status' => 'Planning a trip',
        ];

        $profile = $user->travellerProfile;
        if ($profile) {
            $hobbies = $profile->hobbies ?? [];
            $interests = $profile->interests ?? [];

            $context['user_profile'] = [
                'usia' => $profile->birth_date ? $profile->birth_date->age.' years' : 'unknown',
                'hobi' => $hobbies,
                'minat' => $interests,
                'budget_default' => $profile->default_budget ? 'Rp '.number_format($profile->default_budget, 0, ',', '.') : 'not specified',
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
                'tanggal' => $activeItinerary->start_date->format('d M').' - '.$activeItinerary->end_date->format('d M Y'),
                'durasi' => $activeItinerary->duration_days.' days',
                'peserta' => $activeItinerary->total_participants.' people',
                'budget' => $activeItinerary->budget_input ? 'Rp '.number_format($activeItinerary->budget_input, 0, ',', '.') : 'not specified',
                'status' => $activeItinerary->status === 'ongoing' ? 'in progress' : 'planned',
            ];

            // Include a few itinerary items for reference
            $sampleItems = $activeItinerary->itineraryItems()
                ->select('day_number', 'type', 'name', 'schedule_time')
                ->orderBy('day_number')
                ->orderBy('sort_order')
                ->take(10)
                ->get()
                ->map(fn ($i) => "Day {$i->day_number} {$i->schedule_time} — {$i->name} ({$i->type})")
                ->toArray();

            if (! empty($sampleItems)) {
                $context['jadwal_singkat'] = $sampleItems;
            }
        }

        // Chat via OpenCode with stored session
        try {
            $reply = $openCode->chat($validated['message'], $context);
        } catch (\Exception $e) {
            $replies = [
                'Got it, noted! Would you like to ask about your itinerary, destination, or booking?',
                'For recommendations in '.($activeItinerary?->destination ?? 'your destination').', I suggest checking your itinerary first.',
                'Your active vouchers can be checked on the Today dashboard. How can I help?',
                'Your trip is going smoothly. Need restaurant or attraction recommendations?',
            ];
            $reply = $replies[array_rand($replies)];
        }

        // Save user message to DB
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Beautify the AI reply (markdown → HTML)
        $reply = $this->beautifyChatReply($reply);

        // Save AI reply to DB
        ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'ai',
            'content' => $reply,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'reply' => $reply,
                'raw' => false,
                'time' => now()->format('H:i'),
            ]);
        }

        return back()->with('reply', $reply);
    }

    /**
     * Beautify an AI chat reply by converting markdown-like syntax to clean HTML.
     * Handles: **bold**, *italic*, - bullet lists, numbered lists, and line breaks.
     */
    protected function beautifyChatReply(string $text): string
    {
        // Escape any existing HTML for safety
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $lines = explode("\n", $text);
        $result = [];
        $inUl = false;
        $inOl = false;
        $prevEmpty = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Empty line → paragraph break; close any open lists
            if ($trimmed === '') {
                if ($inUl) {
                    $result[] = '</ul>';
                    $inUl = false;
                }
                if ($inOl) {
                    $result[] = '</ol>';
                    $inOl = false;
                }
                if (! $prevEmpty && ! empty($result)) {
                    $result[] = '<br>';
                }
                $prevEmpty = true;

                continue;
            }
            $prevEmpty = false;

            // Bullet list: "- text" or "* text"
            if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $m)) {
                if (! $inUl) {
                    $result[] = '<ul>';
                    $inUl = true;
                }
                if ($inOl) {
                    $result[] = '</ol>';
                    $inOl = false;
                }
                $result[] = '<li>'.$this->formatInlineMarkdown($m[1]).'</li>';

                continue;
            }

            // Numbered list: "1. text" or "1) text"
            if (preg_match('/^\d+[.)]\s+(.+)$/', $trimmed, $m)) {
                if (! $inOl) {
                    $result[] = '<ol>';
                    $inOl = true;
                }
                if ($inUl) {
                    $result[] = '</ul>';
                    $inUl = false;
                }
                $result[] = '<li>'.$this->formatInlineMarkdown($m[1]).'</li>';

                continue;
            }

            // Close any open lists before a regular paragraph
            if ($inUl) {
                $result[] = '</ul>';
                $inUl = false;
            }
            if ($inOl) {
                $result[] = '</ol>';
                $inOl = false;
            }

            $result[] = '<p>'.$this->formatInlineMarkdown($trimmed).'</p>';
        }

        // Close any open lists at end
        if ($inUl) {
            $result[] = '</ul>';
        }
        if ($inOl) {
            $result[] = '</ol>';
        }

        return implode("\n", $result);
    }

    /**
     * Format inline markdown: **bold** and *italic*.
     */
    protected function formatInlineMarkdown(string $text): string
    {
        // Bold with ** or __
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        // Italic with * (single, not double)
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $text);
        $text = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/', '<em>$1</em>', $text);

        return $text;
    }

    /**
     * Fetch chat message history as JSON for the current user.
     */
    public function history()
    {
        $messages = ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content', 'created_at']);

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'time' => $m->created_at->format('H:i'),
            ]),
        ]);
    }

    /**
     * Mark user activity timestamp.
     */
    protected function touchActivity(): void
    {
        Cache::put('chat_activity_'.Auth::id(), now()->timestamp, 1800);
    }

    /**
     * Check if user is idle (no activity for 10 minutes).
     */
    public function checkIdle()
    {
        $lastActivity = Cache::get('chat_activity_'.Auth::id());
        $isIdle = ! $lastActivity || (now()->timestamp - $lastActivity) > $this->inactivityTimeout;

        return response()->json([
            'idle' => $isIdle,
        ]);
    }

    /**
     * Delete the chat session (called when user is inactive and doesn't respond).
     */
    public function endSession()
    {
        $cacheKey = 'opencode_session_'.Auth::id();
        $sessionId = Cache::get($cacheKey);

        if ($sessionId) {
            try {
                app(OpenCodeClient::class)->deleteSession($sessionId);
            } catch (\Exception $e) {
                // Session might already be expired
            }
            Cache::forget($cacheKey);
        }

        Cache::forget('chat_activity_'.Auth::id());

        return response()->json(['message' => 'Chat session ended.']);
    }
}
