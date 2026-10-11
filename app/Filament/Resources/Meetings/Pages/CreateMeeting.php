<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateMeeting extends CreateRecord
{
    protected static string $resource = MeetingResource::class;

    /** @var array<string, mixed> */
    protected array $recurrenceData = [];

    /** @var array{present: list<int>, late: list<int>} */
    protected array $attendanceData = ['present' => [], 'late' => []];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->recurrenceData = [
            'pattern' => $data['recurrence_pattern'] ?? 'weekly',
            'interval' => (int) ($data['recurrence_interval'] ?? 1),
            'ends_at' => $data['recurrence_ends_at'] ?? null,
        ];

        unset($data['recurrence_pattern'], $data['recurrence_interval'], $data['recurrence_ends_at']);

        $this->attendanceData = [
            'present' => array_map('intval', $data['present_ids'] ?? []),
            'late' => array_map('intval', $data['late_ids'] ?? []),
        ];

        unset($data['present_ids'], $data['late_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncAudienceAllowList();

        if ($this->record->is_recurring) {
            $this->record->recurrence()->create([
                'pattern' => $this->recurrenceData['pattern'],
                'interval' => max(1, $this->recurrenceData['interval']),
                'ends_at' => $this->recurrenceData['ends_at']
                    ? Carbon::parse($this->recurrenceData['ends_at'])->endOfDay()
                    : null,
            ]);
        }

        if (
            $this->record->scheduled_at?->isPast()
            && ($this->attendanceData['present'] !== [] || $this->attendanceData['late'] !== [])
        ) {
            app(AttendanceService::class)->backfillAttendance(
                $this->record,
                auth()->user(),
                $this->attendanceData['present'],
                $this->attendanceData['late'],
            );
        }
    }
}
