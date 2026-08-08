<?php

namespace App\Providers;

use App\Events\EventAttended;
use App\Events\EventRegistered;
use App\Events\MemberRegistered;
use App\Events\MemberSuspended;
use App\Listeners\AwardPointsForEventAttendance;
use App\Listeners\AwardPointsForEventRegistration;
use App\Listeners\LogMembershipActivity;
use App\Listeners\NotifyAdminOfPendingMember;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MemberRegistered::class => [
            SendWelcomeNotification::class,
            NotifyAdminOfPendingMember::class,
        ],
        MemberSuspended::class => [
            //
        ],
        EventRegistered::class => [
            AwardPointsForEventRegistration::class,
        ],
        EventAttended::class => [
            AwardPointsForEventAttendance::class,
        ],
    ];

    protected $subscribe = [
        LogMembershipActivity::class,
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    protected function configureEmailVerification(): void
    {
        // The framework's base EventServiceProvider (registered automatically via
        // withEvents()) already registers SendEmailVerificationNotification for the
        // Registered event. Overriding this avoids double-registering the listener.
    }
}
