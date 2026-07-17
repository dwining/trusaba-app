<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantAvailability extends Model
{
    /** @var string */
    protected $table = 'merchant_availability';

    protected $fillable = [
        'merchant_id',
        'resource_type',
        'resource_id',
        'date',
        'available_qty',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
