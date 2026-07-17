<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()->with('merchant')->latest()->paginate(10);

        return response()->json($bookings);
    }

    public function show(int $id)
    {
        $booking = Auth::user()->bookings()->with(['merchant', 'transaction'])->findOrFail($id);

        return response()->json($booking);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'itinerary_item_id' => ['nullable', 'exists:itinerary_items,id'],
            'merchant_id' => ['required', 'exists:merchants,id'],
            'booking_type' => ['required', 'in:hotel,restaurant,attraction,transport,other'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'booking_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'resource_detail' => ['nullable', 'array'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $booking = $user->bookings()->create([
            'merchant_id' => $validated['merchant_id'],
            'itinerary_item_id' => $validated['itinerary_item_id'] ?? null,
            'booking_type' => $validated['booking_type'],
            'check_in_date' => $validated['check_in_date'] ?? null,
            'check_out_date' => $validated['check_out_date'] ?? null,
            'booking_date' => $validated['booking_date'] ?? now()->toDateString(),
            'quantity' => $validated['quantity'] ?? 1,
            'resource_detail' => $validated['resource_detail'] ?? [],
            'amount' => $validated['amount'],
            'status' => 'pending',
            'voucher_code' => 'TSB-'.strtoupper(Str::random(6)),
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_gateway' => 'midtrans',
            'gateway_trx_id' => 'MOCK-'.strtoupper(Str::random(12)),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $booking->update(['status' => 'confirmed']);

        return response()->json([
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'amount' => $booking->amount,
            'voucher_code' => $booking->voucher_code,
        ], 201);
    }
}
