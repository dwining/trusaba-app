<?php

namespace App\Http\Controllers;

use App\Services\OpenCodeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
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

        // Build context from user profile and active itinerary
        $context = [
            'user_name' => $user->name,
            'role' => $user->role,
        ];

        $profile = $user->travellerProfile;
        if ($profile) {
            $context['destination'] = $profile->hobbies ? 'Bali' : 'Indonesia';
        }

        // Get active itinerary summary
        $activeItinerary = $user->itineraries()
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->first();

        if ($activeItinerary) {
            $context['active_trip'] = [
                'destination' => $activeItinerary->destination,
                'dates' => $activeItinerary->start_date->format('d M').' - '.$activeItinerary->end_date->format('d M Y'),
                'duration' => $activeItinerary->duration_days.' hari',
            ];
        }

        try {
            $reply = $openCode->chat($validated['message'], $context);
        } catch (\Exception $e) {
            // Mock fallback for demo
            $replies = [
                'Baik, aku catat. Ada lagi yang ingin dicek di itinerary?',
                'Kalau butuh ubah jadwal, bilang saja — aku bantu sesuaikan.',
                'Voucher aktifmu masih valid. Butuh arah ke lokasi berikutnya?',
                'Untuk pertanyaan itu, aku sarankan cek dashboard Hari Ini ya.',
                'Trip-mu masih berjalan lancar. Ada yang bisa dibantu?',
            ];
            $reply = $replies[array_rand($replies)];
        }

        // If JSON requested (AJAX from JS), return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'reply' => $reply,
                'time' => now()->format('H:i'),
            ]);
        }

        return back()->with('reply', $reply);
    }
}
