<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;

class TransactionStatusCards extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return TransactionResource::canViewAny();
    }

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->status('All', number_format(Transaction::query()->count()), 'receipt_long', 'all', 'info'),
            $this->status('Pending', number_format(Transaction::query()->pending()->count()), 'pending', 'pending', 'warning'),
            $this->status('Approved', number_format(Transaction::query()->approved()->count()), 'check_circle', 'approved', 'success'),
            $this->status('Rejected', number_format(Transaction::query()->rejected()->count()), 'cancel', 'rejected', 'danger'),
            $this->status('Income', 'UGX '.number_format((float) Transaction::query()->income()->sum('amount'), 0), 'arrow_downward', 'income', 'success'),
            $this->status('Expenses', 'UGX '.number_format((float) Transaction::query()->expense()->sum('amount'), 0), 'arrow_upward', 'expenses', 'danger'),
        ];
    }

    private function status(string $label, string $value, string $icon, string $tab, string $color): Stat
    {
        $isActive = request()->string('tab', 'all')->toString() === $tab;

        return Stat::make($label, $value)
            ->icon($icon)
            ->description('View transactions')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url(TransactionResource::getUrl('index', ['tab' => $tab]))
            ->extraAttributes([
                'class' => 'rounded-sm transition hover:-translate-y-0.5 hover:shadow-md'.($isActive ? ' ring-2 ring-primary-500/30' : ''),
                'aria-current' => $isActive ? 'page' : null,
            ]);
    }

    /** @return array<string, int> */
    protected function getColumns(): array
    {
        return ['default' => 1, 'md' => 2, 'lg' => 3];
    }
}
