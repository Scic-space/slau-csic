<?php

namespace App\Filament\Resources\CtfCompetitions\Pages;

use App\Filament\Resources\CtfCompetitions\CtfCompetitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCtfCompetitions extends ListRecords
{
    protected static string $resource = CtfCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
