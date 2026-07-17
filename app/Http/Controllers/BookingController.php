<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()
            ->with('merchant')
            ->latest()
            ->get();

        return view('booking.index', compact('bookings'));
    }

    public function show(int $id)
    {
        $booking = Auth::user()->bookings()
            ->with(['merchant', 'transaction', 'itinerary', 'itineraryItem'])
            ->findOrFail($id);

        return view('booking.show', compact('booking'));
    }

    public function create(Request $request)
    {
        $merchantId = $request->query('merchant_id');
        $itemId = $request->query('item_id');
        $type = $request->query('type', 'hotel');

        $merchant = $merchantId ? Merchant::with('merchantRooms')->findOrFail($merchantId) : null;

        return view('booking.hotel-detail', compact('merchant', 'itemId', 'type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => ['required', 'exists:merchants,id'],
            'itinerary_item_id' => ['nullable', 'exists:itinerary_items,id'],
            'booking_type' => ['required', 'in:hotel,restaurant,attraction,transport,other'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
            'booking_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'resource_detail' => ['nullable', 'array'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string'],
        ], [
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $user = Auth::user();

        // Create booking
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

        // Create transaction (simulate Midtrans payment — mark as paid for MVP)
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_gateway' => 'midtrans',
            'gateway_trx_id' => 'MOCK-'.strtoupper(Str::random(12)),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update booking status
        $booking->update(['status' => 'confirmed']);

        return redirect()->route('bookings.success', ['id' => $booking->id]);
    }

    public function success(int $id)
    {
        $booking = Auth::user()->bookings()
            ->with(['merchant', 'transaction'])
            ->findOrFail($id);

        return view('booking.booking-success', compact('booking'));
    }

    public function createPayment(Request $request)
    {
        return view('booking.payment');
    }

    public function payment(int $id)
    {
        $booking = Auth::user()->bookings()
            ->with('merchant')
            ->findOrFail($id);

        return view('booking.payment', compact('booking'));
    }
}
