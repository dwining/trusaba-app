<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'destination'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatRoomMessage::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ChatRoomMembership::class);
    }
}
