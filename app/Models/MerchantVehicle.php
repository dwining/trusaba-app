<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantVehicle extends Model
{
    protected $fillable = [
        'merchant_id',
        'vehicle_type',
        'vehicle_name',
        'total_units',
        'price_per_day',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
