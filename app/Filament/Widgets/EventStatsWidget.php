<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->can('view_events') ?? false;
    }

    protected function getStats(): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $upcomingEvents = Event::where('start_date', '>=', $now)
            ->whereIn('status', ['published', 'scheduled'])
            ->count();

        $ongoingEvents = Event::where('status', 'ongoing')->count();

        $eventsThisMonth = Event::whereBetween('start_date', [$monthStart, $monthEnd])->count();

        // Events that ended this month — the denominator for attendance rate
        $eventsEndedThisMonth = Event::whereBetween('end_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['completed', 'ongoing'])
            ->pluck('id');

        $totalRegistrations = \App\Models\EventRegistration::whereIn('event_id', $eventsEndedThisMonth)
            ->where('status', 'registered')
            ->count();

        $totalAttendance = \App\Models\EventRegistration::whereIn('event_id', $eventsEndedThisMonth)
            ->whereNotNull('attended_at')
            ->count();

        $totalEvents = Event::count();

        $attendanceRate = $totalRegistrations > 0
            ? round(($totalAttendance / $totalRegistrations) * 100, 1)
            : 0;

        $categories = EventCategory::withCount('events')->get();
        $topCategory = $categories->sortByDesc('events_count')->first();

        return [
            Stat::make('Upcoming Events', $upcomingEvents)
                ->description($eventsThisMonth > 0 ? $eventsThisMonth.' this month' : 'No events this month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Active / Ongoing', $ongoingEvents)
                ->description($upcomingEvents.' upcoming')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning'),

            Stat::make('Attendance Rate', $attendanceRate.'%')
                ->description($totalAttendance.' of '.$totalRegistrations.' attended')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 70 ? 'success' : ($attendanceRate >= 40 ? 'warning' : 'danger')),

            Stat::make('Total Events', $totalEvents)
                ->description($topCategory ? 'Top: '.$topCategory->name.' ('.$topCategory->events_count.')' : 'No categories')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
