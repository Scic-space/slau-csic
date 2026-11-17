<?php

namespace App\Filament\Resources\AttendanceCorrections\Pages;

use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAttendanceCorrections extends ManageRecords
{
    protected static string $resource = AttendanceCorrectionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
