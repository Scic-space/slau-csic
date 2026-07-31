<?php

use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->competition = CtfCompetition::create([
        'title' => 'Test CTF 2026',
        'slug' => 'test-ctf-2026',
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(6),
    ]);
});

it('shows the CTF index page with all challenges', function () {
    CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Index Challenge',
        'slug' => 'index-challenge',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{index_test}'),
        'points' => 50,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    CtfCompetition::create([
        'title' => 'Second CTF',
        'slug' => 'second-ctf',
        'status' => 'published',
        'is_public' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(6),
    ]);

    $this->actingAs($this->user)
        ->get(route('ctf.index'))
        ->assertSuccessful()
        ->assertSee('Test CTF 2026')
        ->assertSee('Challenges')
        ->assertSee('Second CTF');
});

it('shows a competition page with challenges', function () {
    $challenge = CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Challenge 1',
        'slug' => 'challenge-1',
        'description' => 'A test challenge',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{test_flag}'),
        'points' => 100,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->get(route('ctf.competition', $this->competition))
        ->assertSuccessful()
        ->assertSee('Challenge 1')
        ->assertSee('100')
        ->assertSee('easy');
});

it('rejects an incorrect flag', function () {
    $challenge = CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Challenge 1',
        'slug' => 'challenge-1',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{secret}'),
        'points' => 100,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{wrong}',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('accepts a correct flag and records the solve', function () {
    $challenge = CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Challenge 1',
        'slug' => 'challenge-1',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{correct_flag}'),
        'points' => 100,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{correct_flag}',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($challenge->isSolvedBy($this->user))->toBeTrue();
});

it('rejects duplicate solves for the same challenge', function () {
    $challenge = CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Challenge 1',
        'slug' => 'challenge-1',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{unique_flag}'),
        'points' => 100,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{unique_flag}',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{unique_flag}',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('allows multiple attempts on the same challenge', function () {
    $challenge = CtfChallenge::create([
        'ctf_competition_id' => $this->competition->id,
        'ctf_category_id' => CtfCategory::where('slug', 'web')->first()->id,
        'title' => 'Challenge 1',
        'slug' => 'challenge-1',
        'flag_hash' => hash('sha256', 'SLAU_CSIC{final}'),
        'points' => 100,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    // First try: wrong flag
    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{wrong1}',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    // Second try: wrong flag again
    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{wrong2}',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    // Third try: correct flag
    $this->actingAs($this->user)
        ->post(route('ctf.submit', [$this->competition, $challenge]), [
            'flag' => 'SLAU_CSIC{final}',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($challenge->isSolvedBy($this->user))->toBeTrue();
});
