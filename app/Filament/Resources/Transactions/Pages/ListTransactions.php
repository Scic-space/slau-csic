<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Widgets\TransactionStatusCards;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionStatusCards::class,
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

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),

            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'income')),

            'expenses' => Tab::make('Expenses')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'expense')),
        ];
    }
}
