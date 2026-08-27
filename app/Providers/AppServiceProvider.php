<?php

namespace App\Providers;

use App\Events\NewNotificationBroadcast;
use App\Listeners\CheckNotificationPreferences;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Badge;
use App\Models\BudgetAllocation;
use App\Models\BudgetCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\CtfWriteup;
use App\Models\Election;
use App\Models\Exam;
use App\Models\Fine;
use App\Models\FineAppeal;
use App\Models\FinePayment;
use App\Models\FineType;
use App\Models\Meeting;
use App\Models\RoleTemplate;
use App\Models\Training;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\BadgePolicy;
use App\Policies\BudgetAllocationPolicy;
use App\Policies\BudgetCategoryPolicy;
use App\Policies\CtfChallengePolicy;
use App\Policies\CtfCompetitionPolicy;
use App\Policies\CtfWriteupPolicy;
use App\Policies\ElectionPolicy;
use App\Policies\EventPolicy;
use App\Policies\ExamPolicy;
use App\Policies\FineAppealPolicy;
use App\Policies\FinePaymentPolicy;
use App\Policies\FinePolicy;
use App\Policies\FineTypePolicy;
use App\Policies\MeetingPolicy;
use App\Policies\RoleTemplatePolicy;
use App\Policies\TrainingPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Event::listen(NotificationSending::class, CheckNotificationPreferences::class);

        Event::listen(NotificationSent::class, function (NotificationSent $event): void {
            if ($event->notifiable instanceof \Illuminate\Foundation\Auth\User) {
                if (config('broadcasting.default') !== 'log' && config('broadcasting.default') !== 'null') {
                    try {
                        broadcast(new NewNotificationBroadcast($event->notification))->toOthers();
                    } catch (\Throwable) {
                        // Broadcasting unavailable — silently skip
                    }
                }
            }
        });

        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Fine::class, FinePolicy::class);
        Gate::policy(FineType::class, FineTypePolicy::class);
        Gate::policy(FineAppeal::class, FineAppealPolicy::class);
        Gate::policy(FinePayment::class, FinePaymentPolicy::class);
        Gate::policy(BudgetCategory::class, BudgetCategoryPolicy::class);
        Gate::policy(BudgetAllocation::class, BudgetAllocationPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(CtfCompetition::class, CtfCompetitionPolicy::class);
        Gate::policy(CtfChallenge::class, CtfChallengePolicy::class);
        Gate::policy(CtfWriteup::class, CtfWriteupPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(RoleTemplate::class, RoleTemplatePolicy::class);
        Gate::policy(Meeting::class, MeetingPolicy::class);
        Gate::policy(\App\Models\Event::class, EventPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Badge::class, BadgePolicy::class);
        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
