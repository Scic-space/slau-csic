<?php

namespace App\Filament\Resources\CtfCategories\Pages;

use App\Filament\Resources\CtfCategories\CtfCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCtfCategories extends ManageRecords
{
    protected static string $resource = CtfCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
