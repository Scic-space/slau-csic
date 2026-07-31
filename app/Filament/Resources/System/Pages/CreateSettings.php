<?php

namespace App\Filament\Resources\System\Pages;

use App\Filament\Resources\System\SettingsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSettings extends CreateRecord
{
    protected static string $resource = SettingsResource::class;
}
