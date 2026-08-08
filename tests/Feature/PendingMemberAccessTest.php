<?php

use App\Livewire\CompetitionShow;
use App\Livewire\EventDetails;
use App\Livewire\MyGrades;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows a pending member to reach the dashboard', function () {
    $this->actingAs(User::factory()->pending()->create())
        ->get(route('dashboard'))
        ->assertOk();
});

it('allows a pending member to reach their profile and membership card', function () {
    $user = User::factory()->pending()->create();

    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    $this->actingAs($user)->get(route('membership.card'))->assertOk();
});

it('allows a pending member to reach notifications and support', function () {
    $user = User::factory()->pending()->create();

    $this->actingAs($user)->get(route('notifications.index'))->assertOk();
    $this->actingAs($user)->get(route('support'))->assertOk();
});

it('redirects a pending member to the dashboard when accessing club activities', function (string $routeName) {
    $this->actingAs(User::factory()->pending()->create())
        ->get(route($routeName))
        ->assertRedirect(route('dashboard'));
})->with([
    'events calendar' => 'events.calendar',
    'attendance calendar' => 'attendance.calendar',
    'my events' => 'my-events',
    'grades' => 'grades.index',
    'trainings' => 'trainings.index',
    'resources' => 'resources.index',
    'fines' => 'fines.index',
    'my transactions' => 'my-transactions',
    'account statement' => 'account.statement',
    'exams' => 'exams.index',
    'voting' => 'voting.index',
    'voting nominations' => 'voting.nominations',
    'voting verify' => 'voting.verify.form',
    'polls' => 'polls.index',
    'ctf arena' => 'ctf.index',
    'club classes' => 'portal.classes',
]);

it('returns an inertia location to the dashboard for pending members', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->actingAs(User::factory()->pending()->create())
        ->withHeader('X-Inertia', 'true')
        ->post(route('events.register', $event))
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('dashboard'));
});

it('blocks a pending member from registering for an event', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->actingAs(User::factory()->pending()->create())
        ->post(route('events.register', $event))
        ->assertRedirect(route('dashboard'));

    expect($event->registrations()->count())->toBe(0);
});

it('blocks a pending member from the event check-in page', function () {
    $this->actingAs(User::factory()->pending()->create())
        ->get(route('events.checkin'))
        ->assertRedirect(route('dashboard'));
});

it('allows an approved member to reach club activities', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('trainings.index'))
        ->assertOk();
});

it('allows an approved member to register for an event', function () {
    $event = Event::factory()->create(['status' => 'published']);

    $this->actingAs(User::factory()->create())
        ->post(route('events.register', $event))
        ->assertRedirect();

    expect($event->registrations()->count())->toBe(1);
});

it('redirects a pending member away from gated livewire components', function (string $component) {
    Livewire::actingAs(User::factory()->pending()->create())
        ->test($component)
        ->assertRedirect(route('dashboard'));
})->with([
    'grades' => MyGrades::class,
    'exams' => \App\Livewire\ExamListing::class,
    'trainings' => \App\Livewire\TrainingListing::class,
    'my events' => \App\Livewire\MyEvents::class,
    'my transactions' => \App\Livewire\MyTransactions::class,
    'polls' => \App\Livewire\PollListing::class,
    'attendance calendar' => \App\Livewire\AttendanceCalendar::class,
    'resources' => \App\Livewire\ResourceLibrary::class,
    'certificates' => \App\Livewire\ExamCertificates::class,
    'voting' => \App\Livewire\ElectionVoting::class,
    'verify receipt' => \App\Livewire\VerifyReceipt::class,
    'submit testimonial' => \App\Livewire\SubmitTestimonial::class,
]);

it('redirects a pending member away from event participation actions', function () {
    $event = Event::factory()->create(['status' => 'published']);

    Livewire::actingAs(User::factory()->pending()->create())
        ->test(EventDetails::class, ['event' => $event])
        ->call('register')
        ->assertRedirect(route('dashboard'));

    expect($event->registrations()->count())->toBe(0);
});

it('redirects a pending member away from joining competitions', function () {
    $competition = Competition::factory()->create();

    Livewire::actingAs(User::factory()->pending()->create())
        ->test(CompetitionShow::class, ['competition' => $competition])
        ->call('join')
        ->assertRedirect(route('dashboard'));
});

it('allows approved members to interact with gated livewire components', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(MyGrades::class)
        ->assertOk();
});
