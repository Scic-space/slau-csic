<?php

namespace App\Livewire;

use App\Models\Badge;
use App\Models\Election;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\StaffNotification;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Dashboard')]
class MemberDashboard extends Component
{
    public function render()
    {
        $user = Auth::user()->load([
            'memberProfile',
            'socialLinks',
            'earnedBadges',
            'membership',
            'eventRegistrations' => fn ($q) => $q->with('event')->latest()->limit(5),
            'attendance' => fn ($q) => $q->with('meeting')->latest()->limit(5),
        ]);

        $membership = $user->membership;

        $memberStats = $user->getMemberStats();

        $expiringSoon = $user->membership_expires_at
            && $user->membership_expires_at->isFuture()
            && $user->membership_expires_at->diffInDays(now()) <= 30;

        $stats = [
            'events_attended' => $user->attendance_count ?? 0,
            'total_sessions' => $user->total_sessions_attended ?? 0,
            'current_streak' => $user->current_streak ?? 0,
            'longest_streak' => $user->longest_streak ?? 0,
            'score' => $user->score ?? 0,
            'rank' => $user->rank ?? 'Unranked',
            'attendance_rate' => $memberStats['attendance_rate'],
            'trainings_completed' => $memberStats['trainings_completed'],
            'membership_duration' => $memberStats['membership_duration'],
            'competition_entries' => $memberStats['competition_entries'],
            'projects_led' => $memberStats['projects_led'],
            'projects_participated' => $memberStats['projects_participated'],
            'club_portal_active_tracks' => $memberStats['club_portal_active_tracks'],
            'club_portal_completed_tracks' => $memberStats['club_portal_completed_tracks'],
            'club_portal_average_progress' => $memberStats['club_portal_average_progress'],
            'expiring_soon' => $expiringSoon,
        ];

        $unearnedBadgeCount = Badge::whereDoesntHave('users', fn ($q) => $q->where('user_id', $user->id))->count();

        $joinableMeetings = Meeting::with('creator')
            ->whereNotNull('meeting_link')
            ->whereNull('ended_at')
            ->where(function ($q) {
                $q->where('attendance_open', true)
                    ->orWhere(function ($q) {
                        $q->where('scheduled_at', '>=', now()->subHours(2))
                            ->where('scheduled_at', '<=', now()->addMinutes(30));
                    });
            })
            ->orderBy('scheduled_at')
            ->get();

        $unreadNotifications = StaffNotification::where('staff_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get();

        $unreadNotificationCount = StaffNotification::where('staff_id', $user->id)
            ->where('is_read', false)
            ->count();

        $upcomingEvents = Event::published()
            ->with(['organizer', 'categories'])
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $finesSummary = [
            'total_outstanding' => $user->fines()
                ->whereIn('status', ['pending', 'partially_paid'])
                ->get()
                ->sum(fn ($f) => $f->balance),
            'overdue_count' => $user->fines()->overdue()->count(),
            'pending_count' => $user->fines()->pending()->count(),
        ];

        $activeElections = Election::open()
            ->live()
            ->orderBy('ends_at')
            ->get();

        $activeTrainings = TrainingEnrollment::where('user_id', $user->id)
            ->active()
            ->with('training')
            ->latest('enrolled_at')
            ->limit(5)
            ->get();

        $clubActivity = Activity::query()
            ->with(['causer', 'subject'])
            ->where('causer_id', '!=', $user->id)
            ->whereNotNull('causer_id')
            ->whereIn('description', [
                'Member approved',
                'Member rejected',
                'Member suspended',
                'election_opened',
                'election_closed',
                'election_results_published',
            ])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('livewire.member-dashboard', [
            'user' => $user,
            'membership' => $membership,
            'stats' => $stats,
            'unearnedBadgeCount' => $unearnedBadgeCount,
            'joinableMeetings' => $joinableMeetings,
            'unreadNotifications' => $unreadNotifications,
            'unreadNotificationCount' => $unreadNotificationCount,
            'upcomingEvents' => $upcomingEvents,
            'finesSummary' => $finesSummary,
            'activeElections' => $activeElections,
            'activeTrainings' => $activeTrainings,
            'clubActivity' => $clubActivity,
        ]);
    }

    public function markNotificationAsRead(int $notificationId): void
    {
        $notification = StaffNotification::where('staff_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
