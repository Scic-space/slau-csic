<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeetingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->can('view_meetings') ?? false;
    }

    protected function getStats(): array
    {
        $now = now();

        $totalMeetings = Meeting::count();

        $upcomingMeetings = Meeting::upcoming()->count();

        $ongoingMeetings = Meeting::where('attendance_open', true)->count();

        $pastMeetings = Meeting::past()->count();

        $totalAttendance = \App\Models\Attendance::count();

        $presentCount = \App\Models\Attendance::where('status', 'present')->count();
        $lateCount = \App\Models\Attendance::where('status', 'late')->count();
        $absentCount = \App\Models\Attendance::where('status', 'absent')->count();

        $attendanceRate = $totalAttendance > 0
            ? round((($presentCount + $lateCount) / $totalAttendance) * 100, 1)
            : 0;

        return [
            Stat::make('Total Meetings', $totalMeetings)
                ->description($upcomingMeetings.' upcoming, '.$ongoingMeetings.' ongoing')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('info'),

            Stat::make('Attendance Rate', $attendanceRate.'%')
                ->description(($presentCount + $lateCount).' present/late of '.$totalAttendance)
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 70 ? 'success' : ($attendanceRate >= 40 ? 'warning' : 'danger')),

            Stat::make('Present Today', \App\Models\Attendance::where('status', 'present')->whereDate('checked_in_at', $now->toDateString())->count())
                ->description($lateCount > 0 ? $lateCount.' late today' : 'No late arrivals')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Past Meetings', $pastMeetings)
                ->description($totalMeetings > 0 ? round(($pastMeetings / $totalMeetings) * 100).'% of all meetings' : 'No meetings yet')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
