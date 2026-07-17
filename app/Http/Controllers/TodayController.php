<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TodayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Find active itinerary (confirmed or ongoing)
        $itinerary = $user->itineraries()
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with(['itineraryItems' => fn ($q) => $q->orderBy('day_number')->orderBy('sort_order')])
            ->first();

        $todayItems = collect();
        $dayNumber = null;

        if ($itinerary) {
            $startDate = Carbon::parse($itinerary->start_date);
            $dayNumber = (int) $startDate->diffInDays($today) + 1;

            $todayItems = $itinerary->itineraryItems
                ->where('day_number', $dayNumber)
                ->sortBy('schedule_time');
        }

        // Get active vouchers (confirmed bookings for this trip)
        $vouchers = $user->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with('merchant')
            ->latest()
            ->take(3)
            ->get();

        return view('today', compact('itinerary', 'todayItems', 'dayNumber', 'vouchers', 'today'));
    }
}
