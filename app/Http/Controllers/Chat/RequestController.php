<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatRoomMembership;
use App\Models\PrivateChatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $incoming = PrivateChatRequest::with('fromUser')
            ->where('to_user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $active = PrivateChatRequest::with(['fromUser', 'toUser', 'room'])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
            ->where('status', 'accepted')
            ->latest()
            ->get();

        $sent = PrivateChatRequest::with('toUser')
            ->where('from_user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('chat.requests.index', compact('incoming', 'active', 'sent'));
    }

    public function store(Request $request)
    {
        $request->validate(['to_user_id' => 'required|exists:users,id']);

        $user = Auth::user();

        // Check for existing request
        $existing = PrivateChatRequest::where(function ($q) use ($user, $request) {
            $q->where('from_user_id', $user->id)
                ->where('to_user_id', $request->to_user_id);
        })->orWhere(function ($q) use ($user, $request) {
            $q->where('from_user_id', $request->to_user_id)
                ->where('to_user_id', $user->id);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted' && $existing->chat_room_id) {
                return redirect()->route('chat.private.show', $existing->chat_room_id);
            }

            return back()->with('toast', 'A request already exists.');
        }

        PrivateChatRequest::create([
            'from_user_id' => $user->id,
            'to_user_id' => $request->to_user_id,
            'status' => 'pending',
        ]);

        return back()->with('toast', 'DM request sent!');
    }

    public function accept($id)
    {
        $req = PrivateChatRequest::where('to_user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        // Create private room
        $room = ChatRoom::create([
            'name' => $req->fromUser->name.' & '.$req->toUser->name,
            'slug' => 'dm-'.$req->from_user_id.'-'.$req->to_user_id,
            'type' => 'private',
            'destination' => null,
        ]);

        // Create memberships for both users
        ChatRoomMembership::create([
            'chat_room_id' => $room->id,
            'user_id' => $req->from_user_id,
            'can_send' => true,
        ]);
        ChatRoomMembership::create([
            'chat_room_id' => $room->id,
            'user_id' => $req->to_user_id,
            'can_send' => true,
        ]);

        $req->update(['status' => 'accepted', 'chat_room_id' => $room->id]);

        return redirect()->route('chat.private.show', $room->id)->with('toast', 'DM accepted!');
    }

    public function reject($id)
    {
        $req = PrivateChatRequest::where('to_user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $req->update(['status' => 'rejected']);

        return back()->with('toast', 'Request rejected.');
    }
}
