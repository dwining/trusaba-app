<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateItineraryJob;
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
            ->paginate(10);

        return response()->json($itineraries);
    }

    public function show(int $id)
    {
        $itinerary = Auth::user()->itineraries()
            ->with(['itineraryItems' => fn ($q) => $q->orderBy('day_number')->orderBy('sort_order')])
            ->findOrFail($id);

        return response()->json($itinerary);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'participants' => ['nullable', 'integer', 'min:1', 'max:50'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'hobbies' => ['nullable', 'array'],
            'interests' => ['nullable', 'array'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $itinerary = Auth::user()->itineraries()->create([
            'title' => 'Trip ke '.$validated['destination'],
            'destination' => $validated['destination'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => (int) $startDate->diffInDays($endDate) + 1,
            'total_participants' => $validated['participants'] ?? 1,
            'budget_input' => $validated['budget'] ?? Auth::user()->travellerProfile?->default_budget,
            'status' => 'processing',
        ]);

        GenerateItineraryJob::dispatch($itinerary->id);

        return response()->json([
            'message' => 'Itinerary sedang diproses.',
            'itinerary_id' => $itinerary->id,
            'status' => 'processing',
        ], 202);
    }

    public function status(int $id)
    {
        $itinerary = Auth::user()->itineraries()->select('id', 'status')->findOrFail($id);

        $response = ['itinerary_id' => $itinerary->id, 'status' => $itinerary->status];

        if ($itinerary->status === 'draft' || $itinerary->status === 'completed') {
            $response['itinerary'] = $itinerary->load('itineraryItems');
        }

        return response()->json($response);
    }

    public function update(Request $request, int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'in:confirmed,cancelled'],
        ]);

        $itinerary->update($validated);

        return response()->json(['message' => 'Itinerary berhasil diperbarui.', 'itinerary' => $itinerary]);
    }

    public function destroy(int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        if ($itinerary->bookings()->whereNotIn('status', ['cancelled'])->exists()) {
            return response()->json(['message' => 'Tidak bisa menghapus itinerary dengan booking aktif.'], 400);
        }

        $itinerary->delete();

        return response()->json(['message' => 'Itinerary berhasil dihapus.']);
    }
}
