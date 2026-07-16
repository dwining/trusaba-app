<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('auth')->with('toast', 'Google login belum dikonfigurasi.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('auth')->with('toast', 'Google login belum dikonfigurasi.');
        }

        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->id],
            [
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'avatar' => $googleUser->avatar,
                'role' => 'traveller',
                'email_verified_at' => now(),
            ]
        );

        // If user exists by email but no google_id yet, link them
        if (! $user->wasRecentlyCreated && ! $user->google_id) {
            $user->update(['google_id' => $googleUser->id, 'avatar' => $googleUser->avatar]);
        }

        Auth::login($user);

        if ($user->travellerProfile && $user->travellerProfile->birth_date) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('onboarding');
    }
}
