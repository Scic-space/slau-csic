<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\Fine;
use App\Models\FineType;
use Illuminate\Console\Command;

class AutoApplyFines extends Command
{
    protected $signature = 'fines:auto-apply {--dry-run : Preview without creating fines}';

    protected $description = 'Auto-apply fines for event no-shows';

    public function handle(): int
    {
        $fineTypes = FineType::active()
            ->where('auto_apply_trigger', 'event_no_show')
            ->get();

        if ($fineTypes->isEmpty()) {
            $this->warn('No FineTypes configured with auto_apply_trigger = event_no_show.');

            return self::SUCCESS;
        }

        $events = Event::where('start_date', '<', now())
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('attendanceRecords', function ($q) {
                $q->where('status', 'present');
            })
            ->orWhere(function ($q) {
                $q->where('start_date', '<', now())
                    ->whereNull('cancelled_at')
                    ->where('status', '!=', 'cancelled');
            })
            ->distinct()
            ->get();

        if ($events->isEmpty()) {
            $this->info('No completed events found to process.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($events as $event) {
            $registeredMemberIds = EventRegistration::where('event_id', $event->id)
                ->where('status', 'registered')
                ->whereNull('cancelled_at')
                ->pluck('user_id');

            if ($registeredMemberIds->isEmpty()) {
                continue;
            }

            $attendedMemberIds = EventAttendance::where('event_id', $event->id)
                ->whereIn('status', ['present', 'excused'])
                ->pluck('member_id');

            $noShowIds = $registeredMemberIds->diff($attendedMemberIds);

            if ($noShowIds->isEmpty()) {
                continue;
            }

            $fineType = $fineTypes->first();
            $defaultAmount = $fineType->default_amount;

            foreach ($noShowIds as $memberId) {
                $exists = Fine::where('user_id', $memberId)
                    ->where('fine_type_id', $fineType->id)
                    ->where('reason', 'like', "%(Event #{$event->id})%")
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("[DRY-RUN] Would create fine for user #{$memberId} on event '{$event->title}'");
                    $created++;

                    continue;
                }

                Fine::create([
                    'user_id' => $memberId,
                    'fine_type_id' => $fineType->id,
                    'amount' => $defaultAmount,
                    'reason' => "No-show for event '{$event->title}' (Event #{$event->id})",
                    'issue_date' => now(),
                    'due_date' => now()->addDays($fineType->auto_apply_threshold ?? 14),
                    'status' => 'pending',
                    'amount_paid' => 0,
                    'issued_by' => 1,
                ]);

                $created++;
            }
        }

        if ($this->option('dry-run')) {
            $this->info("[DRY-RUN] Would create {$created} fine(s). {$skipped} already exist(ed).");
        } else {
            $this->info("Created {$created} fine(s) for event no-shows. {$skipped} skipped (already existed).");
        }

        return self::SUCCESS;
    }
}
