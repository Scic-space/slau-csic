<?php

namespace App\Listeners;

use App\Events\EventAttended;
use App\Services\GamificationService;

class AwardPointsForEventAttendance
{
    public function handle(EventAttended $event): void
    {
        app(GamificationService::class)->awardOnce(
            $event->user,
            GamificationService::POINTS_EVENT_ATTENDANCE,
            "Attended event: {$event->event->title}",
            $event->event::class,
            $event->event->id,
        );
    }
}
