<?php

namespace App\Http\Controllers;

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
            ->get();

        return view('itinerary.index', compact('itineraries'));
    }

    public function show(int $id)
    {
        $itinerary = Auth::user()->itineraries()
            ->with(['itineraryItems' => fn ($q) => $q->orderBy('day_number')->orderBy('sort_order')])
            ->findOrFail($id);

        $days = $itinerary->itineraryItems->groupBy('day_number');

        return view('itinerary.show', compact('itinerary', 'days'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'participants' => ['nullable', 'integer', 'min:1', 'max:50'],
            'budget' => ['nullable', 'integer', 'min:0'],
        ], [
            'destination.required' => 'Destinasi wajib diisi.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = (int) $startDate->diffInDays($endDate) + 1;

        $itinerary = Auth::user()->itineraries()->create([
            'destination' => $validated['destination'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'total_participants' => $validated['participants'] ?? 1,
            'budget_input' => $validated['budget'] ?? Auth::user()->travellerProfile?->default_budget,
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

        return redirect()->route('itineraries.show', $id)->with('toast', 'Itinerary berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($id);

        if ($itinerary->bookings()->whereNotIn('status', ['cancelled'])->exists()) {
            return back()->with('toast', 'Tidak bisa menghapus itinerary dengan booking aktif.');
        }

        $itinerary->delete();

        return redirect()->route('itineraries.index')->with('toast', 'Itinerary berhasil dihapus.');
    }
}
