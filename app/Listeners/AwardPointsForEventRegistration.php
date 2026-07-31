<?php

namespace App\Listeners;

use App\Events\EventRegistered;
use App\Services\GamificationService;

class AwardPointsForEventRegistration
{
    public function handle(EventRegistered $event): void
    {
        app(GamificationService::class)->awardPoints(
            $event->user,
            10,
            "Registered for event: {$event->event->title}",
            $event->event::class,
            $event->event->id,
        );
    }
}
