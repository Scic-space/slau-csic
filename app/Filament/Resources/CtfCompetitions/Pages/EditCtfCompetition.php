<?php

namespace App\Filament\Resources\CtfCompetitions\Pages;

use App\Filament\Resources\CtfCompetitions\CtfCompetitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCtfCompetition extends EditRecord
{
    protected static string $resource = CtfCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
