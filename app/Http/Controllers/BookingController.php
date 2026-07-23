<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\Merchant;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->bookings()
            ->with(['merchant', 'itineraryItem']);

        $itineraryId = $request->query('itinerary_id');
        if ($itineraryId) {
            $query->where('itinerary_id', $itineraryId);
        }

        $bookings = $query->latest()->get();
        $itinerary = $itineraryId
            ? Auth::user()->itineraries()->find($itineraryId)
            : null;

        return view('booking.index', compact('bookings', 'itinerary'));
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

        $itineraryItem = $itemId
            ? ItineraryItem::with('itinerary')->find($itemId)
            : null;

        if ($merchantId) {
            $merchant = Merchant::with(['merchantRooms', 'merchantVehicles'])->findOrFail($merchantId);

            // Load itinerary context (via item or directly)
            $itineraryId = $request->query('itinerary_id');
            $itinerary = $itineraryItem?->itinerary
                ?? ($itineraryId ? Itinerary::select('id', 'title', 'destination', 'start_date', 'end_date')->find($itineraryId) : null);

            // Check for explicit date params (additional hotel bookings from coverage gaps)
            $paramCheckIn = $request->query('check_in');
            $paramCheckOut = $request->query('check_out');

            if ($paramCheckIn && $paramCheckOut) {
                $defaultCheckIn = $paramCheckIn;
                $defaultCheckOut = $paramCheckOut;
            } elseif ($itineraryItem?->itinerary) {
                $defaultCheckIn = Carbon::parse($itineraryItem->itinerary->start_date)
                    ->addDays($itineraryItem->day_number - 1)
                    ->format('Y-m-d');
                $defaultCheckOut = Carbon::parse($itineraryItem->itinerary->end_date)->format('Y-m-d');
            } elseif ($itinerary) {
                $defaultCheckIn = Carbon::parse($itinerary->start_date)->format('Y-m-d');
                $defaultCheckOut = Carbon::parse($itinerary->end_date)->format('Y-m-d');
            } else {
                $defaultCheckIn = now()->addDays(7)->format('Y-m-d');
                $defaultCheckOut = Carbon::parse($defaultCheckIn)->addDays(1)->format('Y-m-d');
            }

            return view('booking.detail', compact(
                'merchant',
                'itinerary',
                'itineraryItem',
                'type',
                'defaultCheckIn',
                'defaultCheckOut'
            ));
        }

        // No merchant selected yet — show merchant selection list
        $itineraryId = $request->query('itinerary_id');

        $destination = $itineraryItem?->itinerary?->destination ?? '';
        if (! $destination && $itineraryId) {
            $destination = Itinerary::select('destination')->find($itineraryId)?->destination ?? '';
        }
        $cityHint = trim(explode(',', $destination)[0] ?? '');

        $availableMerchants = Merchant::with(['merchantRooms', 'merchantVehicles'])
            ->where('is_active', true)
            ->where('type', $type)
            ->when($cityHint, fn ($q) => $q->where(
                fn ($q) => $q->where('city', 'LIKE', "%{$cityHint}%")
                    ->orWhere('province', 'LIKE', "%{$cityHint}%")
            ))
            ->limit(10)
            ->get();

        if ($availableMerchants->isEmpty()) {
            $availableMerchants = Merchant::with(['merchantRooms', 'merchantVehicles'])
                ->where('is_active', true)
                ->where('type', $type)
                ->limit(10)
                ->get();
        }

        return view('booking.detail', compact(
            'availableMerchants',
            'itineraryItem',
            'type',
            'itineraryId'
        ));
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
            'amount.required' => 'Payment amount is required.',
            'payment_method.required' => 'Payment method is required.',
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
