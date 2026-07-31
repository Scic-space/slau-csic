<?php

namespace App\Filament\Resources\Competitions\Pages;

use App\Filament\Resources\Competitions\ChallengeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageChallenges extends ManageRecords
{
    protected static string $resource = ChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
