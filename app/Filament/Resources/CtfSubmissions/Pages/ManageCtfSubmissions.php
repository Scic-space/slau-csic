<?php

namespace App\Filament\Resources\CtfSubmissions\Pages;

use App\Filament\Resources\CtfSubmissions\CtfSubmissionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCtfSubmissions extends ManageRecords
{
    protected static string $resource = CtfSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
