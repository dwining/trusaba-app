<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantRoom extends Model
{
    protected $fillable = [
        'merchant_id',
        'room_type',
        'total_rooms',
        'price_per_night',
        'description',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
