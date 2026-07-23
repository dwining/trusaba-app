<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatRoomMembership;
use App\Models\ChatRoomMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivateChatController extends Controller
{
    public function show($id)
    {
        $room = ChatRoom::where('type', 'private')->findOrFail($id);

        // Verify membership
        $isMember = ChatRoomMembership::where('chat_room_id', $room->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $otherUser = ChatRoomMembership::with('user')
            ->where('chat_room_id', $room->id)
            ->where('user_id', '!=', Auth::id())
            ->first()?->user;

        return view('chat.private.show', compact('room', 'otherUser'));
    }

    public function send(Request $request, $id)
    {
        $room = ChatRoom::where('type', 'private')->findOrFail($id);
        $user = Auth::user();

        $isMember = ChatRoomMembership::where('chat_room_id', $room->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $request->validate(['content' => 'required|string|max:1000']);

        $message = ChatRoomMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json([
            'id' => $message->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_initial' => strtoupper(mb_substr(explode(' ', trim($user->name))[0], 0, 3)),
            'content' => $message->content,
            'created_at' => $message->created_at->format('H:i'),
        ]);
    }

    public function history(Request $request, $id)
    {
        $afterId = $request->query('after', 0);
        $messages = ChatRoomMessage::with('user')
            ->where('chat_room_id', $id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'is_mine' => $m->user_id === Auth::id(),
                'user_name' => $m->user->name,
                'user_initial' => strtoupper(mb_substr(explode(' ', trim($m->user->name))[0], 0, 3)),
                'content' => $m->content,
                'created_at' => $m->created_at->format('H:i'),
            ]);

        return response()->json($messages);
    }
}
