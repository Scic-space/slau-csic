<?php

namespace App\Filament\Resources\FineTypes\Pages;

use App\Filament\Resources\FineTypes\FineTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFineTypes extends ManageRecords
{
    protected static string $resource = FineTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
