<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Fine;
use App\Models\Meeting;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $stats = [];

        if ($user->can('view_users')) {
            $totalMembers = User::count();
            $activeMembers = User::where('membership_status', 'active')
                ->where('membership_type', 'active')
                ->count();
            $pendingApproval = User::where('membership_status', 'pending')->count();
            $newThisMonth = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $stats[] = Stat::make('Total Members', $totalMembers)
                ->description($totalMembers > 0 ? 'All registered members' : 'No members yet')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary');

            $stats[] = Stat::make('Active Members', $activeMembers)
                ->description($totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1).'% active rate' : '0%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');

            $stats[] = Stat::make('Pending Approval', $pendingApproval)
                ->description($pendingApproval > 0 ? 'Need attention' : 'All approved')
                ->descriptionIcon($pendingApproval > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingApproval > 0 ? 'warning' : 'success');

            $stats[] = Stat::make('New This Month', $newThisMonth)
                ->description('Joined this month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info');
        }

        if ($user->can('view_events')) {
            $upcomingEvents = Event::published()
                ->where('start_date', '>=', now())
                ->count();

            $eventsThisMonth = Event::published()
                ->whereMonth('start_date', now()->month)
                ->whereYear('start_date', now()->year)
                ->count();

            $stats[] = Stat::make('Upcoming Events', $upcomingEvents)
                ->description($eventsThisMonth.' events this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary');
        }

        if ($user->can('view_meetings')) {
            $upcomingMeetings = Meeting::upcoming()->count();
            $todayMeetings = Meeting::today()->count();

            $stats[] = Stat::make('Upcoming Meetings', $upcomingMeetings)
                ->description($todayMeetings > 0 ? $todayMeetings.' scheduled today' : 'None scheduled today')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('info');
        }

        if ($user->hasAnyRole(['admin', 'super-admin', 'treasurer', 'president'])) {
            $outstandingFines = Fine::whereIn('status', ['pending', 'partially_paid'])->sum('balance');
            $overdueFines = Fine::overdue()->count();

            $stats[] = Stat::make('Outstanding Fines', 'UGX '.number_format($outstandingFines, 0))
                ->description($overdueFines > 0 ? $overdueFines.' overdue' : 'All fines up to date')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($overdueFines > 0 ? 'danger' : 'success');
        }

        return $stats;
    }

    protected function getColumns(): int
    {
        $count = count($this->getStats());

        return min($count, 4);
    }
}
