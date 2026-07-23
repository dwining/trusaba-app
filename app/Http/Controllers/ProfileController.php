<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load('travellerProfile');

        return view('profile.edit', compact('user'));
    }

    public function show()
    {
        $profile = Auth::user()->travellerProfile;

        return response()->json($profile);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:100', 'unique:users,email,'.Auth::id()],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*' => ['string'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
        ], [
            'email.unique' => 'Email is already in use.',
            'birth_date.before' => 'Invalid birth date.',
        ]);

        $user = Auth::user();

        // Update user data
        if (isset($validated['name'])) {
            $user->update(['name' => $validated['name']]);
        }
        if (isset($validated['email'])) {
            $user->update(['email' => $validated['email']]);
        }

        // Update traveller profile
        $user->travellerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'birth_date' => $validated['birth_date'] ?? $user->travellerProfile?->birth_date,
                'phone' => $validated['phone'] ?? $user->travellerProfile?->phone,
                'hobbies' => $validated['hobbies'] ?? $user->travellerProfile?->hobbies ?? [],
                'interests' => $validated['interests'] ?? $user->travellerProfile?->interests ?? [],
            ]
        );

        // Redirect back to the referring page or history
        $redirectTo = $request->input('redirect_to', 'history');

        if ($redirectTo === 'onboarding') {
            return redirect()->route('dashboard')->with('toast', 'Profile saved successfully. Now create your itinerary!');
        }

        return redirect()->route('history')->with('toast', 'Profile saved successfully.');
    }
}
