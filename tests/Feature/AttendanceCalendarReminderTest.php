<?php

use App\Livewire\AttendanceCalendar;
use App\Models\Attendance;
use App\Models\CalendarReminder;
use App\Models\EventRegistration;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows an approved member to create a calendar reminder', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->call('openCreateReminder', '2026-09-10')
        ->set('reminderTitle', 'Study group')
        ->set('reminderColor', 'success')
        ->set('reminderEndsOn', '2026-09-12')
        ->call('saveReminder')
        ->assertHasNoErrors()
        ->assertDispatched('calendar-reminder-saved')
        ->assertSet('isReminderDrawerOpen', false);

    $this->assertDatabaseHas('calendar_reminders', [
        'user_id' => $user->id,
        'title' => 'Study group',
        'color' => 'success',
    ]);
});

it('validates reminder dates and colors', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->set('reminderTitle', '')
        ->set('reminderColor', 'unknown')
        ->set('reminderStartsOn', '2026-09-12')
        ->set('reminderEndsOn', '2026-09-10')
        ->call('saveReminder')
        ->assertHasErrors([
            'reminderTitle' => 'required',
            'reminderColor' => 'in',
            'reminderEndsOn' => 'after_or_equal',
        ]);
});

it('allows members to edit and delete only their own reminders', function () {
    $user = User::factory()->create(['approved_at' => now()]);
    $otherUser = User::factory()->create(['approved_at' => now()]);
    $reminder = CalendarReminder::factory()->for($user)->create();
    $otherReminder = CalendarReminder::factory()->for($otherUser)->create();

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->call('editReminder', $reminder->id)
        ->set('reminderTitle', 'Updated reminder')
        ->call('saveReminder')
        ->call('editReminder', $reminder->id)
        ->call('deleteReminder')
        ->assertDispatched('calendar-reminder-deleted');

    $this->assertDatabaseMissing('calendar_reminders', ['id' => $reminder->id]);
    $this->assertDatabaseHas('calendar_reminders', ['id' => $otherReminder->id]);

    expect(fn () => Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->call('editReminder', $otherReminder->id))
        ->toThrow(ModelNotFoundException::class);
});

it('renders reminders alongside attendance records', function () {
    $user = User::factory()->create(['approved_at' => now()]);
    CalendarReminder::factory()->for($user)->create(['title' => 'Prepare presentation']);

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->assertSee('Prepare presentation')
        ->assertSee('Create Reminder')
        ->assertSee('Click a record to see details');
});

it('renders interactive start and end date picker controls', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->call('openCreateReminder')
        ->assertSeeHtml('id="reminder-start"')
        ->assertSeeHtml('id="reminder-end"')
        ->assertSeeHtml('@click="$el.showPicker?.()"')
        ->assertSeeHtml('aria-label="Open enter start date picker"')
        ->assertSeeHtml('aria-label="Open enter end date picker"')
        ->set('reminderStartsOn', '2026-09-10')
        ->set('reminderEndsOn', '2026-09-12')
        ->assertSet('reminderStartsOn', '2026-09-10')
        ->assertSet('reminderEndsOn', '2026-09-12');
});

it('combines meeting and event attendance in the calendar stats', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $presentMeeting = Meeting::factory()->create(['created_by' => $user->id, 'scheduled_at' => now()->subDay()]);
    $absentMeeting = Meeting::factory()->create(['created_by' => $user->id, 'scheduled_at' => now()->subDays(2)]);

    Attendance::create(['meeting_id' => $presentMeeting->id, 'user_id' => $user->id, 'status' => 'present', 'checked_in_at' => now()->subDay()]);
    Attendance::create(['meeting_id' => $absentMeeting->id, 'user_id' => $user->id, 'status' => 'absent', 'is_auto_absent' => true]);

    EventRegistration::factory()->for($user)->attended()->create();

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->assertViewHas('stats', function (array $stats) {
            return $stats['total'] === 3
                && $stats['present'] === 2
                && $stats['late'] === 0
                && $stats['absent'] === 1
                && $stats['rate'] === 66.67;
        });
});

it('excludes absences from the calendar attendance rate', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $presentMeeting = Meeting::factory()->create(['created_by' => $user->id, 'scheduled_at' => now()->subDay()]);
    $absentMeeting = Meeting::factory()->create(['created_by' => $user->id, 'scheduled_at' => now()->subDays(2)]);

    Attendance::create(['meeting_id' => $presentMeeting->id, 'user_id' => $user->id, 'status' => 'present', 'checked_in_at' => now()->subDay()]);
    Attendance::create(['meeting_id' => $absentMeeting->id, 'user_id' => $user->id, 'status' => 'absent', 'is_auto_absent' => true]);

    Livewire::actingAs($user)
        ->test(AttendanceCalendar::class)
        ->assertViewHas('stats', fn (array $stats) => $stats['rate'] === 50.0);
});
