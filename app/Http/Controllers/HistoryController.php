<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'trips');

        $itineraries = $user->itineraries()
            ->whereIn('status', ['completed', 'ongoing', 'confirmed'])
            ->withCount('itineraryItems')
            ->latest()
            ->get();

        $transactions = $user->transactions()
            ->with('booking.merchant')
            ->latest()
            ->get();

        return view('history', compact('itineraries', 'transactions', 'tab'));
    }
}
