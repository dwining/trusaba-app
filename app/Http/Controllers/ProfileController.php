<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $profile = Auth::user()->travellerProfile;

        return response()->json($profile);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'birth_date' => ['required', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*' => ['string'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'default_budget' => ['nullable', 'integer', 'min:0'],
        ], [
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        $profile = Auth::user()->travellerProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'birth_date' => $validated['birth_date'],
                'phone' => $validated['phone'] ?? null,
                'hobbies' => $validated['hobbies'] ?? [],
                'interests' => $validated['interests'] ?? [],
                'default_budget' => $validated['default_budget'] ?? null,
            ]
        );

        // If this is from the onboarding flow, redirect to itinerary loading
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Profil berhasil disimpan.', 'profile' => $profile]);
        }

        return redirect()->route('itineraries.index');
    }
}
