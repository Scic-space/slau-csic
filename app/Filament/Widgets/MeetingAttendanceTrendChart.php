<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MeetingAttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = 'Meeting Attendance Trends (6 Months)';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_attendance') ?? false;
    }

    protected function getData(): array
    {
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', checked_in_at)"
            : "DATE_FORMAT(checked_in_at, '%Y-%m')";

        $present = \App\Models\Attendance::where('status', 'present')
            ->where('checked_in_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $late = \App\Models\Attendance::where('status', 'late')
            ->where('checked_in_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $absent = \App\Models\Attendance::where('status', 'absent')
            ->where('checked_in_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $presentValues = [];
        $lateValues = [];
        $absentValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');

            $p = $present->where('month', $month)->first();
            $presentValues[] = $p ? $p->count : 0;

            $l = $late->where('month', $month)->first();
            $lateValues[] = $l ? $l->count : 0;

            $a = $absent->where('month', $month)->first();
            $absentValues[] = $a ? $a->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentValues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Late',
                    'data' => $lateValues,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.1)',
                    'borderColor' => 'rgba(234, 179, 8, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentValues,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
