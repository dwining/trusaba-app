<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            'interests' => ['nullable', 'array'],
            'default_budget' => ['nullable', 'integer', 'min:0'],
        ]);

        $profile = Auth::user()->travellerProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['message' => 'Profil berhasil disimpan.', 'profile' => $profile]);
    }
}
