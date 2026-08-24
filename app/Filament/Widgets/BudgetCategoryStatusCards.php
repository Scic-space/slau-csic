<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\BudgetCategory;
use Filament\Widgets\StatsOverviewWidget;

class BudgetCategoryStatusCards extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->status('All', number_format(BudgetCategory::query()->count()), 'category', 'all', 'info'),
            $this->status('Income', number_format(BudgetCategory::query()->income()->count()), 'trending_up', 'income', 'success'),
            $this->status('Expense', number_format(BudgetCategory::query()->expense()->count()), 'trending_down', 'expense', 'danger'),
            $this->status('Active', number_format(BudgetCategory::query()->active()->count()), 'check_circle', 'active', 'primary'),
        ];
    }

    private function status(string $label, string $value, string $icon, string $tab, string $color): Stat
    {
        $isActive = request()->string('tab', 'all')->toString() === $tab;

        return Stat::make($label, $value)
            ->icon($icon)
            ->description('View categories')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url(BudgetCategoryResource::getUrl('index', ['tab' => $tab]))
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
