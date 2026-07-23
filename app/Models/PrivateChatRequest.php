<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateChatRequest extends Model
{
    protected $fillable = ['from_user_id', 'to_user_id', 'chat_room_id', 'status'];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }
}
