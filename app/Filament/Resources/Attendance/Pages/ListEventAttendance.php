<?php

namespace App\Filament\Resources\Attendance\Pages;

use App\Filament\Resources\Attendance\EventAttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListEventAttendance extends ListRecords
{
    protected static string $resource = EventAttendanceResource::class;
}
