<?php

namespace App\Filament\Widgets;

use App\Models\Fine;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'Treasurer', 'President']) ?? false;
    }

    protected function getStats(): array
    {
        $income = Transaction::income()
            ->where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $expenses = Transaction::expense()
            ->where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $pendingTransactions = Transaction::pending()->count();

        $totalOutstanding = Fine::whereIn('status', ['pending', 'partially_paid'])->sum('balance');

        $totalIssued = Fine::sum('amount');
        $totalPaid = Fine::sum('amount_paid');
        $collectionRate = $totalIssued > 0 ? ($totalPaid / $totalIssued) * 100 : 0;

        return [
            Stat::make('Monthly Income', 'UGX '.number_format($income, 0))
                ->description('Approved transactions this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Monthly Expenses', 'UGX '.number_format($expenses, 0))
                ->description('Approved expenses this month')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Pending Approval', $pendingTransactions)
                ->description($pendingTransactions > 0 ? 'Transactions awaiting review' : 'All approved')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingTransactions > 0 ? 'warning' : 'success'),

            Stat::make('Collection Rate', number_format($collectionRate, 1).'%')
                ->description('UGX '.number_format($totalOutstanding, 0).' outstanding')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($collectionRate >= 80 ? 'success' : 'warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
