<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function availability(int $id, Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'type' => ['nullable', 'in:room,vehicle,slot'],
            'resource_id' => ['nullable', 'integer'],
        ]);

        $query = Merchant::findOrFail($id)->merchantAvailability()
            ->where('date', $request->date);

        if ($request->type) {
            $query->where('resource_type', $request->type);
        }
        if ($request->resource_id) {
            $query->where('resource_id', $request->resource_id);
        }

        $availability = $query->first();

        return response()->json([
            'available' => $availability && $availability->available_qty > 0,
            'available_qty' => $availability?->available_qty ?? 0,
        ]);
    }
}
