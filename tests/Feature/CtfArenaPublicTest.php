<?php

use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicArenaCompetition(array $overrides = []): CtfCompetition
{
    return CtfCompetition::factory()->create($overrides);
}

it('renders the ctf arena landing page', function () {
    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('public/CtfLanding'));
});

it('exposes the real status of each competition', function () {
    publicArenaCompetition(['start_date' => now()->subDay(), 'end_date' => now()->addDays(6)]);
    CtfCompetition::factory()->upcoming()->create();
    CtfCompetition::factory()->expired()->create();

    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('competitions', 3)
            ->where('competitions.0.status', 'live')
            ->where('competitions.1.status', 'upcoming')
            ->where('competitions.2.status', 'ended'));
});

it('orders live first, then upcoming, then ended', function () {
    CtfCompetition::factory()->expired()->create();
    CtfCompetition::factory()->upcoming()->create();
    publicArenaCompetition(['start_date' => now()->subDay(), 'end_date' => now()->addDays(6)]);

    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('competitions.0.status', 'live')
            ->where('competitions.1.status', 'upcoming')
            ->where('competitions.2.status', 'ended'));
});

it('excludes draft and private competitions', function () {
    publicArenaCompetition(['start_date' => now()->subDay(), 'end_date' => now()->addDays(6)]);
    CtfCompetition::factory()->draft()->create();
    CtfCompetition::factory()->private()->create();

    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('competitions', 1)
            ->where('competitions.0.status', 'live'));
});

it('marks a competition without end date as live while running', function () {
    publicArenaCompetition(['start_date' => now()->subDay(), 'end_date' => null]);

    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('competitions.0.status', 'live')
            ->whereNull('competitions.0.end_date'));
});

it('exposes public arena stats', function () {
    $competition = publicArenaCompetition(['start_date' => now()->subDay(), 'end_date' => now()->addDays(6)]);
    $challenge = \App\Models\CtfChallenge::factory()->create(['ctf_competition_id' => $competition->id]);

    CtfSubmission::factory()->create([
        'ctf_challenge_id' => $challenge->id,
        'is_correct' => true,
    ]);

    $this->get(route('ctf-arena'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total_competitions', 1)
            ->where('stats.total_solves', 1)
            ->where('stats.total_participants', 1));
});
