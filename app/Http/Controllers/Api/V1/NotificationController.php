<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(
            Auth::user()->notifications()->latest()->paginate(20)
        );
    }

    public function markRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notifikasi ditandai dibaca.']);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Semua notifikasi ditandai dibaca.']);
    }
}
