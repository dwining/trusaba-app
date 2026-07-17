<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'itinerary_id' => ['nullable', 'exists:itineraries,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('file')->store('expenses', 'public');

        $upload = Auth::user()->expenseUploads()->create([
            'file_path' => $path,
            'itinerary_id' => $validated['itinerary_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['message' => 'Bukti transaksi berhasil disimpan.', 'id' => $upload->id], 201);
    }
}
