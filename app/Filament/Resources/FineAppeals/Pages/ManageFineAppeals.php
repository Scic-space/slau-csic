<?php

namespace App\Filament\Resources\FineAppeals\Pages;

use App\Filament\Resources\FineAppeals\FineAppealResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFineAppeals extends ManageRecords
{
    protected static string $resource = FineAppealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
