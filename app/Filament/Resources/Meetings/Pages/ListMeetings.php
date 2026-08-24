<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Widgets\MeetingStatusCards;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MeetingStatusCards::class,
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

            'upcoming' => Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->upcoming()),

            'ongoing' => Tab::make('Ongoing')
                ->modifyQueryUsing(fn (Builder $query) => $query->notCancelled()->where('attendance_open', true)),

            'past' => Tab::make('Past')
                ->modifyQueryUsing(fn (Builder $query) => $query->past()),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->cancelled()),
        ];
    }
}
