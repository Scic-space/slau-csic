<?php

namespace App\Filament\Resources\Attendance\Pages;

use App\Filament\Resources\Attendance\MeetingAttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListMeetingAttendance extends ListRecords
{
    protected static string $resource = MeetingAttendanceResource::class;
}
