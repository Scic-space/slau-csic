<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecurrenceGenerator
{
    /**
     * Maximum number of instances to generate per event per run
     */
    protected const MAX_INSTANCES_PER_EVENT = 52;

    /**
     * Default months ahead to generate
     */
    protected const MONTHS_AHEAD = 3;

    /**
     * Generate future instances for a recurring event
     */
    public function generateInstances(Event $event, int $monthsAhead = self::MONTHS_AHEAD): int
    {
        if (! $event->isRecurring()) {
            return 0;
        }

        $recurrence = $event->recurrence;
        if (! $recurrence) {
            return 0;
        }

        $count = 0;
        $startDate = Carbon::instance($event->start_date);
        $endDate = Carbon::instance($event->end_date);
        $cutoffDate = Carbon::now()->addMonths($monthsAhead);

        // Check if recurrence has ended
        if ($recurrence->ends_at && Carbon::parse($recurrence->ends_at)->isPast()) {
            return 0;
        }

        // Cap the cutoff date at recurrence end
        if ($recurrence->ends_at) {
            $recurrenceEnd = Carbon::parse($recurrence->ends_at);
            if ($recurrenceEnd->isBefore($cutoffDate)) {
                $cutoffDate = $recurrenceEnd;
            }
        }

        $intervalWeeks = match ($recurrence->pattern) {
            'weekly' => 1,
            'biweekly' => 2,
            'monthly' => 4,
            default => 1,
        } * $recurrence->interval;

        $currentStart = $startDate->copy();
        $currentEnd = $endDate->copy();

        while ($currentStart->isBefore($cutoffDate) && $count < self::MAX_INSTANCES_PER_EVENT) {
            // Skip the first occurrence (that's the master event)
            if ($count > 0 || $event->start_date->isFuture()) {
                // Check if this occurrence already exists
                $exists = Event::where('parent_event_id', $event->id)
                    ->whereDate('start_date', $currentStart)
                    ->exists();

                if (! $exists) {
                    $this->createOccurrence($event, $currentStart, $currentEnd);
                    $count++;
                }
            }

            // Advance by interval
            $currentStart = $currentStart->addWeeks($intervalWeeks);
            $currentEnd = $currentEnd->addWeeks($intervalWeeks);
        }

        Log::info("Generated {$count} instances for event {$event->id}");

        return $count;
    }

    /**
     * Regenerate all upcoming occurrences for all recurring events
     */
    public function regenerateUpcoming(): int
    {
        $count = 0;

        $recurringEvents = Event::where('is_recurring', true)
            ->whereNull('parent_event_id')
            ->get();

        foreach ($recurringEvents as $event) {
            $count += $this->generateInstances($event);
        }

        Log::info("Total instances generated: {$count}");

        return $count;
    }

    /**
     * Create an occurrence from a recurring event
     */
    protected function createOccurrence(Event $master, Carbon $startDate, Carbon $endDate): Event
    {
        return Event::create([
            'title' => $master->title,
            'description' => $master->description,
            'type' => $master->type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'location' => $master->location,
            'max_participants' => $master->max_participants,
            'registration_required' => $master->registration_required,
            'is_public' => $master->is_public,
            'registration_deadline' => $master->registration_deadline,
            'status' => 'scheduled',
            'organizer_id' => $master->organizer_id,
            'requirements' => $master->requirements,
            'registration_fee' => $master->registration_fee,
            'external_link' => $master->external_link,
            'is_recurring' => false, // Occurrences are not recurrence masters
            'parent_event_id' => $master->id,
        ]);
    }

    /**
     * Delete future occurrences for an event
     */
    public function deleteUpcomingOccurrences(Event $event): int
    {
        return Event::where('parent_event_id', $event->id)
            ->where('start_date', '>=', now())
            ->delete();
    }
}
