<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

class ManageAnnouncements extends ManageRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Announcement')
                ->icon(self::materialIcon('add_circle'))
                ->modalHeading('Create announcement')
                ->modalDescription('Share an update with the right members and choose when it becomes visible.')
                ->modalWidth(Width::FiveExtraLarge)
                ->modalSubmitAction(fn (Action $action): Action => $action
                    ->label('Create Announcement')
                    ->icon(self::materialIcon('campaign'))
                    ->extraAttributes(['class' => 'announcement-primary-action']))
                ->modalCancelAction(fn (Action $action): Action => $action
                    ->label('Cancel')
                    ->icon(self::materialIcon('close'))),
        ];
    }

    private static function materialIcon(string $icon): HtmlString
    {
        return new HtmlString('<span class="material-symbols-outlined" aria-hidden="true">'.e($icon).'</span>');
    }
}
