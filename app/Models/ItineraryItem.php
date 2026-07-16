<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItineraryItem extends Model
{
    protected $fillable = [
        'itinerary_id',
        'day_number',
        'schedule_time',
        'type',
        'name',
        'description',
        'location',
        'estimated_cost',
        'is_bookable',
        'merchant_id',
        'booking_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_bookable' => 'boolean',
            'schedule_time' => 'datetime',
        ];
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
