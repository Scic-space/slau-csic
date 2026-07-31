<?php

namespace App\Filament\Resources\System\Pages;

use App\Filament\Resources\System\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;
}
