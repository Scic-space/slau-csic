<?php

namespace App\Filament\Resources\Attendance\Pages;

use App\Filament\Resources\Attendance\EventAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventAttendance extends EditRecord
{
    protected static string $resource = EventAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
