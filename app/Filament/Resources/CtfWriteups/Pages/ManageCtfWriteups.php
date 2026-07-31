<?php

namespace App\Filament\Resources\CtfWriteups\Pages;

use App\Filament\Resources\CtfWriteups\CtfWriteupResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCtfWriteups extends ManageRecords
{
    protected static string $resource = CtfWriteupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
