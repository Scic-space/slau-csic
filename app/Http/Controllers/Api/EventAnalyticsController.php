<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;

class EventAnalyticsController extends Controller
{
    public function summary(Request $request)
    {
        if (! $request->user()->can('view_event_analytics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        $totalEvents = Event::count();

        $upcomingEvents = Event::where('start_date', '>=', $now)
            ->whereIn('status', ['published', 'scheduled'])
            ->count();

        $eventsThisMonth = Event::whereBetween('start_date', [$monthStart, $now])->count();

        $completedEvents = Event::whereIn('status', ['completed', 'ongoing'])
            ->where('end_date', '<=', $now)
            ->get();

        $totalRegistrations = 0;
        $totalAttendance = 0;
        $popularEvents = [];

        foreach ($completedEvents as $event) {
            $regCount = $event->registrations()->where('status', 'registered')->count();
            $attCount = $event->attendanceRecords()->where('status', 'present')->count();
            $totalRegistrations += $regCount;
            $totalAttendance += $attCount;

            $popularEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'registered_count' => $regCount,
                'attended_count' => $attCount,
            ];
        }

        $attendanceRate = $totalRegistrations > 0
            ? round(($totalAttendance / $totalRegistrations) * 100, 1)
            : 0;

        $activeMembers = User::activeMembers()
            ->where('membership_status', 'active')
            ->count();

        $inactiveMembers = User::where('membership_status', 'active')
            ->whereDoesntHave('eventAttendance', function ($q) use ($monthStart) {
                $q->where('created_at', '>=', $monthStart);
            })
            ->count();

        usort($popularEvents, fn ($a, $b) => $b['registered_count'] <=> $a['registered_count']);
        $popularEvents = array_slice($popularEvents, 0, 5);

        $pendingApprovals = EventRegistration::where('status', 'registered')
            ->whereHas('event', fn ($q) => $q->where('registration_type', 'approval_required'))
            ->count();

        $draftEvents = Event::where('status', 'draft')->count();

        return response()->json([
            'total_events' => $totalEvents,
            'upcoming_events' => $upcomingEvents,
            'events_this_month' => $eventsThisMonth,
            'total_registrations' => $totalRegistrations,
            'total_attendance' => $totalAttendance,
            'attendance_rate' => $attendanceRate,
            'active_members' => $activeMembers,
            'inactive_members_this_month' => $inactiveMembers,
            'popular_events' => $popularEvents,
            'pending_approvals' => $pendingApprovals,
            'draft_events' => $draftEvents,
        ]);
    }

    public function memberHistory(User $member, Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser->id !== $member->id && ! $currentUser->can('view_member_history')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $registrations = $member->eventRegistrations()
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        $attendance = $member->eventAttendance()
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        $attendedIds = $attendance->pluck('event_id')->unique();

        $stats = [
            'total_registered' => $registrations->count(),
            'total_attended' => $attendance->where('status', 'present')->count(),
            'total_cancelled' => $registrations->where('status', 'cancelled')->count(),
            'unique_events_attended' => $attendedIds->count(),
            'attendance_rate' => $registrations->count() > 0
                ? round(($attendance->where('status', 'present')->count() / $registrations->count()) * 100, 1)
                : 0,
        ];

        return response()->json([
            'member' => ['id' => $member->id, 'name' => $member->name],
            'stats' => $stats,
            'registrations' => $registrations->map(fn ($reg) => [
                'event' => ['id' => $reg->event->id, 'title' => $reg->event->title, 'slug' => $reg->event->slug],
                'status' => $reg->status,
                'registered_at' => $reg->registered_at,
            ]),
            'attendance' => $attendance->map(fn ($att) => [
                'event' => ['id' => $att->event->id, 'title' => $att->event->title, 'slug' => $att->event->slug],
                'status' => $att->status,
                'checked_in_at' => $att->checked_in_at,
            ]),
        ]);
    }
}
