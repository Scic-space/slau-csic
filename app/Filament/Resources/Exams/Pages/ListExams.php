<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Widgets\ExamStatusCards;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Exam')
                ->icon(new HtmlString('<span class="material-symbols-outlined" aria-hidden="true">add_circle</span>')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamStatusCards::class,
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

            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),

            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }
}
