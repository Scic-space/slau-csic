<?php

namespace App\Filament\Widgets;

use App\Models\CtfSubmission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CtfParticipationChart extends ChartWidget
{
    protected ?string $heading = 'CTF Participation (6 Months)';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false;
    }

    protected function getData(): array
    {
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $correct = CtfSubmission::where('is_correct', true)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $incorrect = CtfSubmission::where('is_correct', false)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $correctValues = [];
        $incorrectValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');

            $c = $correct->where('month', $month)->first();
            $correctValues[] = $c ? $c->count : 0;

            $ic = $incorrect->where('month', $month)->first();
            $incorrectValues[] = $ic ? $ic->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Correct Submissions',
                    'data' => $correctValues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Incorrect Submissions',
                    'data' => $incorrectValues,
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
