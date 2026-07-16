<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'birth_date',
        'phone',
        'hobbies',
        'interests',
        'default_budget',
    ];

    protected function casts(): array
    {
        return [
            'hobbies' => 'array',
            'interests' => 'array',
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
