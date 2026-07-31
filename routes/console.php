<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('assignments:check-deadlines')->dailyAt('8am');
Schedule::command('sessions:auto-status')->everyMinute();
Schedule::command('meetings:auto-close')->everyFiveMinutes();
Schedule::command('meetings:generate-recurring')->dailyAt('1am');
Schedule::command('meetings:send-reminders --type=24h')->dailyAt('8am');
Schedule::command('meetings:send-reminders --type=1h')->hourly();
Schedule::command('events:generate-recurring')->dailyAt('1am');
Schedule::command('events:send-reminders')->dailyAt('8am');
Schedule::command('badge:check')->dailyAt('2am');
Schedule::command('members:auto-suspend-alumni')->dailyAt('3am');
Schedule::command('members:check-expired')->dailyAt('4am');
Schedule::command('budget:check-alerts')->dailyAt('6am');
Schedule::command('fines:auto-apply')->dailyAt('2am');
Schedule::command('fines:send-reminders --type=overdue')->dailyAt('7am');
Schedule::command('fines:send-reminders --type=due-soon')->dailyAt('8am');
Schedule::command('elections:auto-close')->everyFiveMinutes();
Schedule::command('elections:auto-open')->everyFiveMinutes();
Schedule::command('elections:send-reminders')->dailyAt('9am');
Schedule::command('elections:auto-publish-results')->everyFiveMinutes();
