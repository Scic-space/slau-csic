<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\MeetingRecurrence;
use Illuminate\Console\Command;

class GenerateRecurringMeetings extends Command
{
    protected $signature = 'meetings:generate-recurring';

    protected $description = 'Generate future instances for recurring meetings';

    public function handle(): int
    {
        $this->info('Generating recurring meeting instances...');

        $recurrences = MeetingRecurrence::with('meeting')->get();
        $generated = 0;

        foreach ($recurrences as $recurrence) {
            $meeting = $recurrence->meeting;

            if (! $meeting || $meeting->isCancelled()) {
                continue;
            }

            $occurrences = Meeting::where('parent_meeting_id', $meeting->id)
                ->whereNull('cancelled_at')
                ->where('scheduled_at', '>', now()->addMonth())
                ->count();

            if ($occurrences >= 5) {
                continue;
            }

            $lastOccurrence = Meeting::where('parent_meeting_id', $meeting->id)
                ->whereNull('cancelled_at')
                ->orderBy('scheduled_at', 'desc')
                ->first();

            $lastDate = $lastOccurrence?->scheduled_at ?? $meeting->scheduled_at;

            $nextDate = match ($recurrence->pattern) {
                'weekly' => $lastDate->copy()->addWeeks($recurrence->interval),
                'biweekly' => $lastDate->copy()->addWeeks(2 * $recurrence->interval),
                'monthly' => $lastDate->copy()->addMonths($recurrence->interval),
                default => null,
            };

            if (! $nextDate) {
                continue;
            }

            if ($recurrence->ends_at && $nextDate->isAfter($recurrence->ends_at)) {
                continue;
            }

            Meeting::create([
                'title' => $meeting->title,
                'description' => $meeting->description,
                'type' => $meeting->type,
                'scheduled_at' => $nextDate,
                'location' => $meeting->location,
                'meeting_link' => $meeting->meeting_link,
                'duration_minutes' => $meeting->duration_minutes,
                'expected_attendees' => $meeting->expected_attendees,
                'late_threshold_minutes' => $meeting->late_threshold_minutes,
                'created_by' => $meeting->created_by,
                'parent_meeting_id' => $meeting->id,
                'attendance_open' => false,
            ]);

            $generated++;
        }

        $this->info("Generated {$generated} new meeting instances.");

        return Command::SUCCESS;
    }
}
