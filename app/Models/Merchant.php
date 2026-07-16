<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'address',
        'city',
        'province',
        'country',
        'phone',
        'description',
        'logo',
        'is_active',
        'wallet_balance',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function merchantRooms(): HasMany
    {
        return $this->hasMany(MerchantRoom::class);
    }

    public function merchantVehicles(): HasMany
    {
        return $this->hasMany(MerchantVehicle::class);
    }

    public function merchantAvailability(): HasMany
    {
        return $this->hasMany(MerchantAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(ItineraryItem::class);
    }

    public function officerMerchants(): HasMany
    {
        return $this->hasMany(OfficerMerchant::class);
    }
}
