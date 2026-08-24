<?php

namespace App\Filament\Resources\BudgetCategories\Pages;

use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use App\Filament\Widgets\BudgetCategoryStatusCards;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBudgetCategories extends ListRecords
{
    protected static string $resource = BudgetCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BudgetCategoryStatusCards::class,
        ];
    }

    public function getTabsContentComponent(): Component
    {
        return parent::getTabsContentComponent()->hidden();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'income')),

            'expense' => Tab::make('Expense')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'expense')),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
        ];
    }
}
