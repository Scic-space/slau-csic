<?php

namespace App\Filament\Resources\RoleTemplates\Pages;

use App\Filament\Resources\RoleTemplates\RoleTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRoleTemplates extends ManageRecords
{
    protected static string $resource = RoleTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
