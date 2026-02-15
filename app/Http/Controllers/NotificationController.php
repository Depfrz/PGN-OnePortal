<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        // Get latest 10 notifications
        $notifications = $user->notifications()->latest()->take(10)->get()->map(function($n) {
            return [
                'id' => $n->id,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ];
        });
        
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back();
    }
}
