<?php

use App\Models\Announcement;
use App\Models\Competition;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public home page', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
});

it('renders the projects page', function () {
    Project::factory()->create(['name' => 'Test Project']);

    $response = $this->get(route('projects'));

    $response->assertSuccessful();
});

it('renders the competitions page', function () {
    Competition::factory()->create(['name' => 'Test Competition']);

    $response = $this->get(route('competitions.index'));

    $response->assertSuccessful();
});

it('renders the announcements page', function () {
    $user = User::factory()->create();

    Announcement::create([
        'title' => 'Welcome Message',
        'content' => 'Club is starting new projects.',
        'type' => 'general',
        'audience' => 'all',
        'is_published' => true,
        'published_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->get(route('announcements.index'));

    $response->assertSuccessful();
});

it('renders the leaderboard page', function () {
    $response = $this->get(route('leaderboard.index'));

    $response->assertSuccessful();
});

it('shows approved members in the public directory', function () {
    User::factory()->create([
        'name' => 'Doreen Nanyonga',
        'email' => 'doreen@example.com',
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(4),
        'headline' => 'Digital forensics learner',
        'bio' => 'Focused on evidence handling, structured investigation, and club challenge work.',
        'privacy_settings' => [
            'show_profile' => true,
            'show_program' => true,
            'show_year' => true,
        ],
    ]);

    $response = $this->get(route('members.public'));

    $response->assertSuccessful();
    $response->assertSee('Doreen Nanyonga');
});

it('shows the public profile of an approved visible member', function () {
    $member = User::factory()->create([
        'name' => 'Paul Kato',
        'email' => 'paul@example.com',
        'membership_status' => 'active',
        'approved_at' => now(),
        'joined_at' => now()->subMonths(2),
        'headline' => 'CTF participant',
        'bio' => 'Building practical experience through club labs, competition practice, and secure development exercises.',
        'privacy_settings' => [
            'show_profile' => true,
            'show_program' => true,
            'show_year' => true,
        ],
    ]);

    $response = $this->get(route('members.public.show', $member));

    $response->assertSuccessful();
    $response->assertSee('Paul Kato');
    $response->assertSee('CTF participant');
});
