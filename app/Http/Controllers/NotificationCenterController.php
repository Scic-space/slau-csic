<?php

namespace App\Http\Controllers;

use App\Models\StaffNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationCenterController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = StaffNotification::where('staff_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
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
                'read_at' => $n->read_at?->toISOString(),
            ]);

        $unreadCount = StaffNotification::where('staff_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return Inertia::render('members/Notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, StaffNotification $notification): RedirectResponse
    {
        abort_unless($notification->staff_id === $request->user()->id, 403);

        $notification->markAsRead();

        return redirect()->back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        StaffNotification::where('staff_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back();
    }
}
