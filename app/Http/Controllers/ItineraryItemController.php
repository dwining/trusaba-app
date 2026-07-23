<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use App\Models\ItineraryItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItineraryItemController extends Controller
{
    /**
     * Add a new itinerary item.
     */
    public function store(Request $request, $itineraryId)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($itineraryId);

        $validated = $request->validate([
            'day_number' => ['required', 'integer', 'min:1', 'max:'.$itinerary->duration_days],
            'schedule_time' => ['required', 'date_format:H:i'],
            'type' => ['required', 'string', 'in:hotel,restaurant,attraction,transport,shopping,other'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'estimated_cost' => ['nullable', 'integer', 'min:0'],
        ]);

        // Auto-calculate sort_order (append at end of the day)
        $maxSort = ItineraryItem::where('itinerary_id', $itineraryId)
            ->where('day_number', $validated['day_number'])
            ->max('sort_order') ?? 0;

        $item = ItineraryItem::create([
            'itinerary_id' => $itineraryId,
            'day_number' => $validated['day_number'],
            'schedule_time' => $validated['schedule_time'].':00',
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? 0,
            'sort_order' => $maxSort + 1,
            'is_bookable' => false, // user-added items are not bookable
        ]);

        return back()->with('toast', 'Activity added successfully.');
    }

    /**
     * Update an existing itinerary item.
     */
    public function update(Request $request, $itineraryId, $itemId)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($itineraryId);
        $item = $itinerary->itineraryItems()->findOrFail($itemId);

        // Guard: can't edit past items
        $timeOnly = substr((string) $item->schedule_time, -8);
        $itemDateTime = Carbon::parse($itinerary->start_date)
            ->addDays($item->day_number - 1)
            ->format('Y-m-d').' '.$timeOnly;

        if (Carbon::parse($itemDateTime)->isPast()) {
            return back()->with('toast', 'Cannot edit. This activity has already passed.');
        }

        $validated = $request->validate([
            'day_number' => ['sometimes', 'integer', 'min:1', 'max:'.$itinerary->duration_days],
            'schedule_time' => ['sometimes', 'date_format:H:i'],
            'type' => ['sometimes', 'string', 'in:hotel,restaurant,attraction,transport,shopping,other'],
            'name' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'estimated_cost' => ['nullable', 'integer', 'min:0'],
        ]);

        // Don't allow changing type for matched items (has merchant_id)
        if ($item->merchant_id && isset($validated['type']) && $validated['type'] !== $item->type) {
            unset($validated['type']);
        }

        $updateData = array_filter($validated, fn ($key) => $key !== '_token', ARRAY_FILTER_USE_KEY);

        // Ensure estimated_cost is never null (ConvertEmptyStringsToNull middleware)
        if (array_key_exists('estimated_cost', $updateData) && $updateData['estimated_cost'] === null) {
            $updateData['estimated_cost'] = 0;
        }

        // Handle schedule_time format
        if (isset($updateData['schedule_time']) && strlen($updateData['schedule_time']) === 5) {
            $updateData['schedule_time'] .= ':00';
        }

        $item->update($updateData);

        return back()->with('toast', 'Activity updated successfully.');
    }

    /**
     * Delete an itinerary item.
     */
    public function destroy($itineraryId, $itemId)
    {
        $itinerary = Auth::user()->itineraries()->findOrFail($itineraryId);
        $item = $itinerary->itineraryItems()->findOrFail($itemId);

        // Guard 1: can't delete items with active bookings
        if ($item->bookings()->whereIn('status', ['pending', 'confirmed', 'checked_in'])->exists()) {
            return back()->with('toast', 'Cannot delete. This item has active bookings.');
        }

        // Guard 2: can't delete past items
        $timeOnly = substr((string) $item->schedule_time, -8);
        $itemDateTime = Carbon::parse($itinerary->start_date)
            ->addDays($item->day_number - 1)
            ->format('Y-m-d').' '.$timeOnly;

        if (Carbon::parse($itemDateTime)->isPast()) {
            return back()->with('toast', 'Cannot delete. This activity has already passed.');
        }

        $item->delete();

        return back()->with('toast', 'Activity deleted.');
    }
}
