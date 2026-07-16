<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseUpload extends Model
{
    protected $fillable = [
        'user_id',
        'itinerary_id',
        'booking_id',
        'file_path',
        'amount',
        'description',
        'is_processed',
    ];

    protected function casts(): array
    {
        return [
            'is_processed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
