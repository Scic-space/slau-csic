<?php

use App\Livewire\AttendanceCalendar;
use App\Models\CalendarReminder;
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
