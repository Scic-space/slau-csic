<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueOverviewChart extends ChartWidget
{
    protected ?string $heading = 'Revenue vs Expenses (6 Months)';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'treasurer', 'president']) ?? false;
    }

    protected function getData(): array
    {
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date)"
            : "DATE_FORMAT(date, '%Y-%m')";

        $income = Transaction::income()
            ->where('status', 'approved')
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $expenses = Transaction::expense()
            ->where('status', 'approved')
            ->where('date', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $incomeValues = [];
        $expenseValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');

            $inc = $income->where('month', $month)->first();
            $incomeValues[] = $inc ? (float) $inc->total : 0;

            $exp = $expenses->where('month', $month)->first();
            $expenseValues[] = $exp ? (float) $exp->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $incomeValues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseValues,
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
