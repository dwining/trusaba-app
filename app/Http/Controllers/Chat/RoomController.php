<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatRoomMembership;
use App\Models\ChatRoomMessage;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get destinations from user's itineraries
        $userDestinations = Itinerary::where('user_id', $user->id)
            ->whereNotNull('destination')
            ->distinct()
            ->pluck('destination')
            ->map(fn ($d) => trim(explode(',', $d)[0]))
            ->unique()
            ->toArray();

        // Get rooms: user's destinations + 5 random others
        $userRooms = ChatRoom::whereIn('destination', $userDestinations)
            ->where('type', 'group')
            ->get();

        $otherRooms = ChatRoom::where('type', 'group')
            ->whereNotIn('id', $userRooms->pluck('id'))
            ->inRandomOrder()
            ->take(5)
            ->get();

        $rooms = $userRooms->merge($otherRooms);

        // Mark which rooms user can send in
        $memberRoomIds = ChatRoomMembership::where('user_id', $user->id)
            ->where('can_send', true)
            ->pluck('chat_room_id')
            ->toArray();

        // Auto-create memberships for user's destination rooms
        foreach ($userRooms as $room) {
            ChatRoomMembership::firstOrCreate(
                ['chat_room_id' => $room->id, 'user_id' => $user->id],
                ['can_send' => true]
            );
            if (! in_array($room->id, $memberRoomIds)) {
                $memberRoomIds[] = $room->id;
            }
        }

        return view('chat.rooms.index', compact('rooms', 'memberRoomIds'));
    }

    public function show($id)
    {
        $room = ChatRoom::findOrFail($id);
        $user = Auth::user();

        $canSend = ChatRoomMembership::where('chat_room_id', $room->id)
            ->where('user_id', $user->id)
            ->where('can_send', true)
            ->exists();

        // For group rooms: auto-create membership on view (view-only, not can_send)
        if ($room->type === 'group') {
            ChatRoomMembership::firstOrCreate(
                ['chat_room_id' => $room->id, 'user_id' => $user->id],
                ['can_send' => false]
            );
        }

        return view('chat.rooms.show', compact('room', 'canSend'));
    }

    public function send(Request $request, $id)
    {
        $room = ChatRoom::findOrFail($id);
        $user = Auth::user();

        $canSend = ChatRoomMembership::where('chat_room_id', $room->id)
            ->where('user_id', $user->id)
            ->where('can_send', true)
            ->exists();

        if (! $canSend) {
            return response()->json(['error' => 'You cannot send messages in this room.'], 403);
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
