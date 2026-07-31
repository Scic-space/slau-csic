<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffNotification;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $notifications = StaffNotification::where('staff_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'priority' => $n->priority,
                'action_required' => $n->action_required,
                'action_url' => $n->action_url,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at->toISOString(),
            ]);

        return response()->json(['data' => $notifications]);
    }

    public function markRead(Request $request, StaffNotification $notification)
    {
        if ($notification->staff_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        StaffNotification::where('staff_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function unreadCount(Request $request)
    {
        $count = StaffNotification::where('staff_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['data' => ['unread_count' => $count]]);
    }
}
