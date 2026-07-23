<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateItineraryJob;
use App\Models\Booking;
use App\Models\CartItem;
use App\Models\MerchantRoom;
use App\Models\MerchantVehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItineraryController extends Controller
{
    public function index()
    {
        $itineraries = Auth::user()->itineraries()
            ->withCount('itineraryItems')
            ->latest()
            ->get();

        return view('itinerary.index', compact('itineraries'));
    }

    public function show(int $id)
    {
        $itinerary = Auth::user()->itineraries()
            ->with(['itineraryItems' => fn ($q) => $q->orderBy('day_number')->orderBy('schedule_time')->orderBy('sort_order')])
            ->findOrFail($id);

        $days = $itinerary->itineraryItems->groupBy('day_number');

        // Lowest real prices for bookable but unmatched items
        $cityHint = trim(explode(',', $itinerary->destination)[0] ?? '');
        $lowestPrices = [];
        if ($cityHint) {
            $lowestPrices['hotel'] = MerchantRoom::whereHas(
                'merchant', fn ($q) => $q->where('city', 'LIKE', "%{$cityHint}%")->where('is_active', true)
            )->min('price_per_night') ?? 0;
            $lowestPrices['transport'] = MerchantVehicle::whereHas(
                'merchant', fn ($q) => $q->where('city', 'LIKE', "%{$cityHint}%")->where('is_active', true)
            )->min('price_per_day') ?? 0;
        }

        // Hotel coverage tracking
        $hotelBookings = null;
        $totalNights = max(0, (int) Carbon::parse($itinerary->start_date)->diffInDays(Carbon::parse($itinerary->end_date)));
        $coveredNights = [];
        $gaps = [];

        if ($totalNights > 0) {
            $hotelBookings = Booking::where('itinerary_id', $id)
                ->where('booking_type', 'hotel')
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->get();

            // Also include cart items (not yet checked out) in coverage
            $cartHotels = CartItem::where('itinerary_id', $id)
                ->where('booking_type', 'hotel')
                ->where('user_id', Auth::id())
                ->get();

            $start = Carbon::parse($itinerary->start_date);
            $end = Carbon::parse($itinerary->end_date);

            // Mark each night as covered (1) or not (0)
            for ($d = 0; $d < $totalNights; $d++) {
                $nightDate = $start->copy()->addDays($d)->format('Y-m-d');
                $covered = false;
                foreach ($hotelBookings as $booking) {
                    if ($booking->check_in_date && $booking->check_out_date) {
                        $ci = Carbon::parse($booking->check_in_date)->format('Y-m-d');
                        $co = Carbon::parse($booking->check_out_date)->format('Y-m-d');
                        if ($nightDate >= $ci && $nightDate < $co) {
                            $covered = true;
                            break;
                        }
                    }
                }
                if (! $covered) {
                    foreach ($cartHotels as $cartItem) {
                        if ($cartItem->check_in_date && $cartItem->check_out_date) {
                            $ci = Carbon::parse($cartItem->check_in_date)->format('Y-m-d');
                            $co = Carbon::parse($cartItem->check_out_date)->format('Y-m-d');
                            if ($nightDate >= $ci && $nightDate < $co) {
                                $covered = true;
                                break;
                            }
                        }
                    }
                }
                $coveredNights[$nightDate] = $covered;
            }

            // Group consecutive uncovered nights into gap ranges
            $gapStart = null;
            foreach ($coveredNights as $date => $covered) {
                if (! $covered) {
                    if ($gapStart === null) {
                        $gapStart = $date;
                    }
                } else {
                    if ($gapStart !== null) {
                        $gaps[] = [
                            'start' => $gapStart,
                            'end' => Carbon::parse($date)->format('Y-m-d'),
                        ];
                        $gapStart = null;
                    }
                }
            }
            if ($gapStart !== null) {
                $gaps[] = [
                    'start' => $gapStart,
                    'end' => $end->addDay()->format('Y-m-d'), // end date is exclusive
                ];
            }
        }

        // Total from info-only (not bookable) items
        $infoOnlyTotal = $itinerary->itineraryItems()
            ->where('is_bookable', false)
            ->sum('estimated_cost');

        // Total from already booked/paid items
        $bookedTotal = Booking::where('itinerary_id', $id)
            ->whereIn('status', ['confirmed', 'checked_in', 'completed', 'pending'])
            ->sum('amount');

        return view('itinerary.show', compact('itinerary', 'days', 'lowestPrices', 'hotelBookings', 'coveredNights', 'totalNights', 'gaps', 'infoOnlyTotal', 'bookedTotal'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'participants' => ['nullable', 'integer', 'min:1', 'max:50'],
            'budget' => ['nullable', 'integer', 'min:0'],
            // Profile data from onboarding
            'birth_date' => ['nullable', 'date', 'before:today'],
            'hobbies' => ['nullable', 'array'],
            'interests' => ['nullable', 'array'],
        ], [
            'destination.required' => 'Destination is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
        ]);

        $user = Auth::user();

        // Save profile data if provided
        $profileData = [];
        if (isset($validated['birth_date'])) {
            $profileData['birth_date'] = $validated['birth_date'];
        }
        if (isset($validated['hobbies'])) {
            $profileData['hobbies'] = $validated['hobbies'];
        }
        if (isset($validated['interests'])) {
            $profileData['interests'] = $validated['interests'];
        }
        if (! empty($profileData)) {
            $user->travellerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = (int) $startDate->diffInDays($endDate) + 1;

        $itinerary = $user->itineraries()->create([
            'title' => 'Trip to '.$validated['destination'],
            'destination' => $validated['destination'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'total_participants' => $validated['participants'] ?? 1,
            'budget_input' => $validated['budget'] ?? null,
            'status' => 'processing',
        ]);

        GenerateItineraryJob::dispatch($itinerary->id);

        return redirect()->route('itineraries.loading', ['id' => $itinerary->id]);
    }

    public function loading(int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        return view('itinerary.loading', compact('itinerary'));
    }

    public function status(int $id)
    {
        $itinerary = Auth::user()->itineraries()->select('id', 'status')->findOrFail($id);

        return response()->json([
            'itinerary_id' => $itinerary->id,
            'status' => $itinerary->status,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'in:confirmed,cancelled'],
        ]);

        $itinerary->update($validated);

        return redirect()->route('itineraries.show', $id)->with('toast', 'Itinerary updated successfully.');
    }

    public function destroy(int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        if ($itinerary->bookings()->whereNotIn('status', ['cancelled'])->exists()) {
            return back()->with('toast', 'Cannot delete itinerary with active bookings.');
        }

        $itinerary->delete();

        return redirect()->route('itineraries.index')->with('toast', 'Itinerary deleted successfully.');
    }
}
