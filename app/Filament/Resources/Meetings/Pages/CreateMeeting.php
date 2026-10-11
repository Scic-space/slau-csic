<?php

namespace App\Filament\Resources\Meetings\Pages;

use App\Filament\Resources\Meetings\MeetingResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateMeeting extends CreateRecord
{
    protected static string $resource = MeetingResource::class;

    /** @var array<string, mixed> */
    protected array $recurrenceData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->recurrenceData = [
            'pattern' => $data['recurrence_pattern'] ?? 'weekly',
            'interval' => (int) ($data['recurrence_interval'] ?? 1),
            'ends_at' => $data['recurrence_ends_at'] ?? null,
        ];

        unset($data['recurrence_pattern'], $data['recurrence_interval'], $data['recurrence_ends_at']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->record->is_recurring) {
            return;
        }

        $this->record->recurrence()->create([
            'pattern' => $this->recurrenceData['pattern'],
            'interval' => max(1, $this->recurrenceData['interval']),
            'ends_at' => $this->recurrenceData['ends_at']
                ? Carbon::parse($this->recurrenceData['ends_at'])->endOfDay()
                : null,
        ]);
    }
}
