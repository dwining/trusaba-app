<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SosController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $sos = Auth::user()->sosLogs()->create([
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'message' => $validated['message'] ?? 'SOS alert from traveller.',
            'status' => 'open',
        ]);

        return response()->json(['message' => 'SOS alert sent.', 'sos_id' => $sos->id], 201);
    }
}
