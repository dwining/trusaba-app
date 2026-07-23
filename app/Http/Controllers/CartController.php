<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CartItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Show the cart page with all pending items grouped by itinerary.
     */
    public function index()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['merchant', 'itinerary', 'itineraryItem'])
            ->get()
            ->groupBy('itinerary_id');

        $total = $cartItems->flatten()->sum('amount');

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Add an item to the cart from the hotel/attraction detail page.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => ['required', 'integer', 'exists:merchants,id'],
            'itinerary_id' => ['required', 'integer', 'exists:itineraries,id'],
            'itinerary_item_id' => ['nullable', 'integer', 'exists:itinerary_items,id'],
            'booking_type' => ['required', 'string'],
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'room_type' => ['nullable', 'string'],
            'amount' => ['required', 'integer', 'min:1000'],
        ]);

        $user = Auth::user();

        // Check for overlapping hotel bookings/cart items for the same itinerary
        if ($validated['booking_type'] === 'hotel' && isset($validated['check_in_date'], $validated['check_out_date'])) {
            $checkIn = $validated['check_in_date'];
            $checkOut = $validated['check_out_date'];

            // Check existing bookings
            $overlapBooking = Booking::where('itinerary_id', $validated['itinerary_id'])
                ->where('booking_type', 'hotel')
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
                ->exists();

            if (! $overlapBooking) {
                // Check existing cart items (exclude current item if updating)
                $overlapCart = CartItem::where('itinerary_id', $validated['itinerary_id'])
                    ->where('booking_type', 'hotel')
                    ->where('user_id', $user->id)
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn)
                    ->exists();
            }

            if ($overlapBooking || ($overlapCart ?? false)) {
                return back()
                    ->withInput()
                    ->with('toast', 'Date range overlaps with an existing booking. Please choose different dates.');
            }
        }

        // Build resource_detail from available fields
        $resourceDetail = [];
        if ($validated['room_type'] ?? null) {
            $resourceDetail['room_type'] = $validated['room_type'];
        }
        if ($validated['check_in_date'] ?? null) {
            $resourceDetail['nights'] = $this->calculateNights(
                $validated['check_in_date'],
                $validated['check_out_date'] ?? $validated['check_in_date']
            );
        }

        // Prevent duplicate: same itinerary_item_id → update instead of insert
        if ($validated['itinerary_item_id'] ?? null) {
            CartItem::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'itinerary_item_id' => $validated['itinerary_item_id'],
                ],
                [
                    'itinerary_id' => $validated['itinerary_id'],
                    'merchant_id' => $validated['merchant_id'],
                    'booking_type' => $validated['booking_type'],
                    'check_in_date' => $validated['check_in_date'] ?? null,
                    'check_out_date' => $validated['check_out_date'] ?? null,
                    'resource_detail' => $resourceDetail ?: null,
                    'amount' => $validated['amount'],
                ]
            );
        } else {
            CartItem::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'merchant_id' => $validated['merchant_id'],
                    'check_in_date' => $validated['check_in_date'] ?? null,
                    'itinerary_id' => $validated['itinerary_id'],
                    'itinerary_item_id' => null,
                ],
                [
                    'booking_type' => $validated['booking_type'],
                    'check_out_date' => $validated['check_out_date'] ?? null,
                    'resource_detail' => $resourceDetail ?: null,
                    'amount' => $validated['amount'],
                ]
            );
        }

        return redirect()->route('itineraries.show', $validated['itinerary_id'])
            ->with('toast', 'Added to cart!');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove($id)
    {
        $item = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();

        return back()->with('toast', 'Removed from cart.');
    }

    /**
     * Checkout: create one Transaction + multiple Bookings, then clear cart.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'travelers' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('toast', 'Your cart is empty.');
        }

        $totalAmount = $cartItems->sum('amount');
        $itineraryId = $cartItems->first()->itinerary_id;

        // Update itinerary participant count
        $itinerary = $user->itineraries()->find($itineraryId);
        if ($itinerary) {
            $itinerary->update(['total_participants' => (int) $request->travelers]);
        }

        // Create ONE transaction for all bookings
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => $totalAmount,
            'payment_method' => 'midtrans',
            'payment_gateway' => 'midtrans',
            'gateway_trx_id' => 'TRX-'.strtoupper(Str::random(16)),
            'status' => 'paid', // MVP: mock payment
            'paid_at' => now(),
        ]);

        // Create bookings from cart items
        $bookingIds = [];
        foreach ($cartItems as $item) {
            $booking = Booking::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'itinerary_id' => $item->itinerary_id,
                'merchant_id' => $item->merchant_id,
                'itinerary_item_id' => $item->itinerary_item_id,
                'booking_type' => $item->booking_type,
                'check_in_date' => $item->check_in_date,
                'check_out_date' => $item->check_out_date,
                'resource_detail' => $item->resource_detail,
                'amount' => $item->amount,
                'status' => 'confirmed',
                'voucher_code' => 'TRU-'.strtoupper(Str::random(8)),
            ]);

            $bookingIds[] = $booking->id;
        }

        // Clear cart
        CartItem::where('user_id', $user->id)->delete();

        return redirect()->route('bookings.success', ['id' => $bookingIds[0]])
            ->with('checkout_bookings', $bookingIds)
            ->with('toast', count($bookingIds).' bookings confirmed!');
    }

    protected function calculateNights(string $checkIn, string $checkOut): int
    {
        $ci = Carbon::parse($checkIn);
        $co = Carbon::parse($checkOut);

        return max(1, (int) $ci->diffInDays($co));
    }
}
