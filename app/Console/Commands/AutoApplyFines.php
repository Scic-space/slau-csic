<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Fine;
use App\Models\FineType;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\FineIssuedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutoApplyFines extends Command
{
    protected $signature = 'fines:auto-apply {--dry-run : Preview without creating fines}';

    protected $description = 'Auto-apply fines for event no-shows and missed meetings';

    public function handle(): int
    {
        $eventFineType = FineType::active()
            ->where('auto_apply_trigger', 'event_no_show')
            ->first();

        $meetingFineType = FineType::active()
            ->where('auto_apply_trigger', 'missed_meetings')
            ->first();

        if (! $eventFineType) {
            $this->warn('No active FineType with auto_apply_trigger = event_no_show.');
        }

        if (! $meetingFineType) {
            $this->warn('No active FineType with auto_apply_trigger = missed_meetings.');
        }

        [$eventCreated, $eventSkipped] = $this->processEventNoShows($eventFineType);
        [$meetingCreated, $meetingSkipped] = $this->processMissedMeetings($meetingFineType);

        $created = $eventCreated + $meetingCreated;
        $skipped = $eventSkipped + $meetingSkipped;

        $prefix = $this->option('dry-run') ? '[DRY-RUN] Would create' : 'Created';

        $this->info("{$prefix} {$created} fine(s). {$skipped} skipped (already existed).");

        return self::SUCCESS;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function processEventNoShows(?FineType $fineType): array
    {
        if (! $fineType) {
            return [0, 0];
        }

        $events = Event::query()
            ->whereNotNull('no_show_fine_amount')
            ->where('start_date', '<', now())
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->with('registrations')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($events as $event) {
            $registeredIds = $event->registrations
                ->where('status', 'registered')
                ->pluck('user_id')
                ->filter();

            if ($registeredIds->isEmpty()) {
                continue;
            }

            $attendedIds = EventAttendance::where('event_id', $event->id)
                ->whereIn('status', ['present', 'excused'])
                ->pluck('member_id');

            $noShowIds = $registeredIds->diff($attendedIds);

            if ($noShowIds->isEmpty()) {
                continue;
            }

            [$eventCreated, $eventSkipped] = $this->createFines(
                fineType: $fineType,
                memberIds: $noShowIds,
                amount: $event->no_show_fine_amount,
                reference: "(Event #{$event->id})",
                reason: "No-show for event '{$event->title}'",
                issuerId: $event->organizer_id,
            );

            $created += $eventCreated;
            $skipped += $eventSkipped;
        }

        if ($created === 0 && $skipped === 0) {
            $this->info('No completed events with a no-show fine amount to process.');
        }

        return [$created, $skipped];
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function processMissedMeetings(?FineType $fineType): array
    {
        if (! $fineType) {
            return [0, 0];
        }

        $meetings = Meeting::query()
            ->whereNotNull('missed_fine_amount')
            ->notCancelled()
            ->with(['allowedAttendees', 'attendance'])
            ->get()
            ->filter(fn (Meeting $meeting) => $meeting->getEndTime() !== null && $meeting->getEndTime()->isPast());

        $created = 0;
        $skipped = 0;

        foreach ($meetings as $meeting) {
            $expectedIds = $this->expectedMemberIds($meeting);

            if ($expectedIds->isEmpty()) {
                continue;
            }

            $attendedIds = $meeting->attendance
                ->whereIn('status', ['present', 'late'])
                ->pluck('user_id');

            $absentIds = $expectedIds->diff($attendedIds);

            if ($absentIds->isEmpty()) {
                continue;
            }

            [$meetingCreated, $meetingSkipped] = $this->createFines(
                fineType: $fineType,
                memberIds: $absentIds,
                amount: $meeting->missed_fine_amount,
                reference: "(Meeting #{$meeting->id})",
                reason: "Missed meeting '{$meeting->title}'",
                issuerId: $meeting->created_by,
            );

            $created += $meetingCreated;
            $skipped += $meetingSkipped;
        }

        if ($created === 0 && $skipped === 0) {
            $this->info('No ended meetings with a missed fine amount to process.');
        }

        return [$created, $skipped];
    }

    protected function expectedMemberIds(Meeting $meeting): Collection
    {
        $allowedAttendees = $meeting->allowedAttendees;

        if ($allowedAttendees->isNotEmpty()) {
            return $allowedAttendees->pluck('id')->filter();
        }

        return User::activeMembers()->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $memberIds
     * @return array{0: int, 1: int}
     */
    protected function createFines(
        FineType $fineType,
        Collection $memberIds,
        float $amount,
        string $reference,
        string $reason,
        int $issuerId,
    ): array {
        $created = 0;
        $skipped = 0;

        foreach ($memberIds as $memberId) {
            $exists = Fine::where('user_id', $memberId)
                ->where('fine_type_id', $fineType->id)
                ->where('reason', 'like', "%{$reference}%")
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[DRY-RUN] Would create fine for user #{$memberId}: {$reason} ({$reference})");
                $created++;

                continue;
            }

            $fine = Fine::create([
                'user_id' => $memberId,
                'fine_type_id' => $fineType->id,
                'amount' => $amount,
                'reason' => "{$reason} {$reference}",
                'issue_date' => now(),
                'due_date' => now()->addDays($fineType->auto_apply_threshold ?? 14),
                'status' => 'pending',
                'amount_paid' => 0,
                'issued_by' => $issuerId,
            ]);

            $fine->user->notify(new FineIssuedNotification($fine));

            $created++;
        }

        return [$created, $skipped];
    }
}
