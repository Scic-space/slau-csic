<?php

use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\CtfHint;
use App\Models\CtfHintPurchase;
use App\Models\CtfTeam;
use App\Models\CtfTeamMember;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\CtfService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ───

function createCompetition(array $overrides = []): CtfCompetition
{
    return CtfCompetition::factory()->create($overrides);
}

function createChallenge(CtfCompetition $competition, string $flag = 'SLAU_CSIC{test_flag}', array $overrides = []): CtfChallenge
{
    return CtfChallenge::factory()
        ->withFlag($flag)
        ->create(array_merge(['ctf_competition_id' => $competition->id], $overrides));
}

// ─── Team Tests ───

describe('Team Operations', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->competition = createCompetition(['allow_teams' => true, 'max_team_size' => 3]);
        $this->user = User::factory()->create();
    });

    it('creates a team and assigns captain', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        expect($team)->toBeInstanceOf(CtfTeam::class);
        expect($team->name)->toBe('Alpha Squad');
        expect($team->captain_id)->toBe($this->user->id);
        expect($team->invite_code)->not->toBeNull();
        expect($team->isMember($this->user))->toBeTrue();
        expect($team->isCaptain($this->user))->toBeTrue();
    });

    it('creates team member with captain role', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        $captainMember = CtfTeamMember::where('ctf_team_id', $team->id)
            ->where('user_id', $this->user->id)
            ->first();

        expect($captainMember->role)->toBe('captain');
    });

    it('prevents creating a second team for the same competition', function () {
        $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        $this->service->createTeam($this->competition, $this->user, 'Bravo Squad');
    })->throws(\RuntimeException::class, 'You are already in a team');

    it('joins a team by invite code', function () {
        $team = $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');
        $newUser = User::factory()->create();

        $result = $this->service->joinTeam($team, $newUser);

        expect($result['success'])->toBeTrue();
        expect($team->isMember($newUser))->toBeTrue();
        expect($team->members()->count())->toBe(2);
    });

    it('prevents joining if already in a team', function () {
        $team1 = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');
        $team2 = $this->service->createTeam($this->competition, User::factory()->create(), 'Bravo Squad');

        $result = $this->service->joinTeam($team2, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('in_team');
    });

    it('prevents joining a closed team', function () {
        $team = $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');
        $team->update(['is_open' => false]);
        $newUser = User::factory()->create();

        $result = $this->service->joinTeam($team, $newUser);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('team_closed');
    });

    it('prevents joining a full team', function () {
        $team = $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');

        // Fill to max capacity (3)
        $this->service->joinTeam($team, User::factory()->create());
        $this->service->joinTeam($team, User::factory()->create());

        $extraUser = User::factory()->create();
        $result = $this->service->joinTeam($team, $extraUser);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('team_full');
    });

    it('allows member to leave team', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');
        $member = User::factory()->create();
        $this->service->joinTeam($team, $member);

        $result = $this->service->leaveTeam($this->competition, $member);

        expect($result['success'])->toBeTrue();
        expect($team->fresh()->isMember($member))->toBeFalse();
    });

    it('prevents captain from leaving without transferring', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        $result = $this->service->leaveTeam($this->competition, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('is_captain');
    });

    it('disbands team as captain', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');
        $this->service->joinTeam($team, User::factory()->create());

        $result = $this->service->disbandTeam($this->competition, $this->user);

        expect($result['success'])->toBeTrue();
        expect(CtfTeam::find($team->id))->toBeNull();
    });

    it('prevents non-captain from disbanding', function () {
        $team = $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');
        $member = User::factory()->create();
        $this->service->joinTeam($team, $member);

        $result = $this->service->disbandTeam($this->competition, $member);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('not_captain');
    });

    it('transfers captaincy', function () {
        $newCaptain = User::factory()->create();
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');
        $this->service->joinTeam($team, $newCaptain);

        $result = $this->service->transferCaptaincy($team, $this->user, $newCaptain);

        expect($result['success'])->toBeTrue();
        expect($team->fresh()->captain_id)->toBe($newCaptain->id);

        $newCaptainMember = CtfTeamMember::where('ctf_team_id', $team->id)
            ->where('user_id', $newCaptain->id)
            ->first();
        expect($newCaptainMember->role)->toBe('captain');

        $oldCaptainMember = CtfTeamMember::where('ctf_team_id', $team->id)
            ->where('user_id', $this->user->id)
            ->first();
        expect($oldCaptainMember->role)->toBe('member');
    });

    it('prevents transferring to non-member', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');
        $outsider = User::factory()->create();

        $result = $this->service->transferCaptaincy($team, $this->user, $outsider);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('not_member');
    });

    it('prevents transferring to self', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        $result = $this->service->transferCaptaincy($team, $this->user, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('already_captain');
    });

    it('gets user team for competition', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'Alpha Squad');

        $userTeam = $this->service->getUserTeam($this->competition, $this->user);

        expect($userTeam->id)->toBe($team->id);
    });

    it('returns null when user has no team', function () {
        $userTeam = $this->service->getUserTeam($this->competition, $this->user);

        expect($userTeam)->toBeNull();
    });

    it('generates auto slug and invite code on creation', function () {
        $team = $this->service->createTeam($this->competition, $this->user, 'My Cool Team');

        expect($team->slug)->toBe('my-cool-team');
        expect($team->invite_code)->not->toBeNull();
        expect(strlen($team->invite_code))->toBe(16);
    });

    it('prevents duplicate team name in same competition', function () {
        $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');

        $this->service->createTeam($this->competition, User::factory()->create(), 'Alpha Squad');
    })->throws(\Illuminate\Database\QueryException::class);
});

// ─── Hint Purchase Tests ───

describe('Hint Purchases', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->competition = createCompetition();
        $this->user = User::factory()->create();
        $this->challenge = createChallenge($this->competition);
    });

    it('purchases a free hint', function () {
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Look at the HTTP headers',
            'cost' => 0,
        ]);

        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeTrue();
        expect($result['hint'])->toBe('Look at the HTTP headers');
        expect($result['tier'])->toBe(0);
        expect($result['points_spent'])->toBe(0);
    });

    it('purchases a paid hint and deducts points', function () {
        PointTransaction::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'reason' => 'Test points',
        ]);

        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Look at the HTTP headers',
            'cost' => 50,
        ]);

        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeTrue();
        expect($result['points_spent'])->toBe(50);
        expect($this->user->fresh()->total_points)->toBe(450);
    });

    it('enforces sequential tier purchase', function () {
        PointTransaction::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'reason' => 'Test points',
        ]);

        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Hint 0',
            'cost' => 0,
        ]);
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 1,
            'content' => 'Hint 1',
            'cost' => 25,
        ]);

        // Purchase tier 0
        $result1 = $this->service->purchaseHint($this->challenge, $this->user);
        expect($result1['success'])->toBeTrue();
        expect($result1['tier'])->toBe(0);

        // Purchase tier 1
        $result2 = $this->service->purchaseHint($this->challenge, $this->user);
        expect($result2['success'])->toBeTrue();
        expect($result2['tier'])->toBe(1);
    });

    it('returns error when all tiers purchased', function () {
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Hint 0',
            'cost' => 0,
        ]);

        $this->service->purchaseHint($this->challenge, $this->user);

        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('all_purchased');
    });

    it('returns error when insufficient points', function () {
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Expensive hint',
            'cost' => 50,
        ]);

        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('insufficient_points');
    });

    it('returns error when challenge already solved', function () {
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Hint 0',
            'cost' => 0,
        ]);

        $this->service->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('already_solved');
    });

    it('returns error when no hints exist', function () {
        $result = $this->service->purchaseHint($this->challenge, $this->user);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('no_hint');
    });

    it('records purchase in database with correct tier', function () {
        PointTransaction::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'reason' => 'Test points',
        ]);

        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Hint 0',
            'cost' => 10,
        ]);

        $this->service->purchaseHint($this->challenge, $this->user);

        $this->assertDatabaseHas('ctf_hint_purchases', [
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $this->user->id,
            'hint_tier' => 0,
            'points_spent' => 10,
        ]);
    });

    it('allows multiple tier purchases on same challenge', function () {
        PointTransaction::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'reason' => 'Test points',
        ]);

        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 0,
            'content' => 'Hint 0',
            'cost' => 10,
        ]);
        CtfHint::create([
            'ctf_challenge_id' => $this->challenge->id,
            'tier' => 1,
            'content' => 'Hint 1',
            'cost' => 20,
        ]);

        $this->service->purchaseHint($this->challenge, $this->user);
        $this->service->purchaseHint($this->challenge, $this->user);

        expect(CtfHintPurchase::where('ctf_challenge_id', $this->challenge->id)
            ->where('user_id', $this->user->id)
            ->count())->toBe(2);
    });
});

// ─── Dependency Chain Tests ───

describe('Challenge Dependencies', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->competition = createCompetition();
        $this->user = User::factory()->create();
        $this->category = CtfCategory::factory()->create();
    });

    it('marks challenge as dependencies met when no prerequisite', function () {
        $challenge = createChallenge($this->competition);

        expect($challenge->areDependenciesMet($this->user))->toBeTrue();
    });

    it('marks dependencies as not met when prerequisite unsolved', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{main}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        expect($challenge->areDependenciesMet($this->user))->toBeFalse();
    });

    it('marks dependencies as met when prerequisite is solved', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{main}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        $this->service->submitFlag($prereq, $this->user, 'SLAU_CSIC{prereq}');

        expect($challenge->fresh()->areDependenciesMet($this->user))->toBeTrue();
    });

    it('blocks flag submission when dependency not met', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{main}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{main}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('dependency_not_met');
        expect($result['message'])->toContain($prereq->title);
    });

    it('allows flag submission when dependency is met', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{main}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        $this->service->submitFlag($prereq, $this->user, 'SLAU_CSIC{prereq}');
        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{main}');

        expect($result['success'])->toBeTrue();
    });

    it('returns correct dependent count', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $dep1 = createChallenge($this->competition, 'SLAU_CSIC{dep1}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);
        $dep2 = createChallenge($this->competition, 'SLAU_CSIC{dep2}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        expect($prereq->dependents()->count())->toBe(2);
    });

    it('returns prerequisite via dependsOn relationship', function () {
        $prereq = createChallenge($this->competition, 'SLAU_CSIC{prereq}');
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{main}', [
            'depends_on_challenge_id' => $prereq->id,
        ]);

        expect($challenge->dependsOn->id)->toBe($prereq->id);
    });
});

// ─── Dynamic Scoring Tests ───

describe('Dynamic Scoring', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->competition = createCompetition();
        $this->category = CtfCategory::factory()->create();
    });

    it('returns static points when dynamic scoring is disabled', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 500,
            'dynamic_scoring' => false,
        ]);

        expect($challenge->getDynamicPoints())->toBe(500);
    });

    it('returns max points when no solves', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 500,
            'min_points' => 100,
            'decay_factor' => 20,
            'dynamic_scoring' => true,
            'solve_count' => 0,
        ]);

        expect($challenge->getDynamicPoints())->toBe(500);
    });

    it('decreases points as solves increase', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 500,
            'min_points' => 100,
            'decay_factor' => 20,
            'dynamic_scoring' => true,
        ]);

        // With 0 solves, should be max
        expect($challenge->getDynamicPoints())->toBe(500);

        // With 5 solves
        $challenge->update(['solve_count' => 5]);
        $pointsAt5 = $challenge->getDynamicPoints();
        expect($pointsAt5)->toBeLessThan(500);
        expect($pointsAt5)->toBeGreaterThan(100);

        // With 20 solves - should be lower
        $challenge->update(['solve_count' => 20]);
        $pointsAt20 = $challenge->getDynamicPoints();
        expect($pointsAt20)->toBeLessThan($pointsAt5);

        // With 100 solves - should be near min
        $challenge->update(['solve_count' => 100]);
        $pointsAt100 = $challenge->getDynamicPoints();
        expect($pointsAt100)->toBeGreaterThanOrEqual(100);
        expect($pointsAt100)->toBeLessThan($pointsAt20);
    });

    it('never goes below min_points', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 500,
            'min_points' => 100,
            'decay_factor' => 10,
            'dynamic_scoring' => true,
            'solve_count' => 1000,
        ]);

        expect($challenge->getDynamicPoints())->toBeGreaterThanOrEqual(100);
    });

    it('awards correct dynamic points on solve', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 500,
            'min_points' => 100,
            'decay_factor' => 20,
            'dynamic_scoring' => true,
            'solve_count' => 5,
        ]);

        $user = User::factory()->create();
        $expectedPoints = $challenge->getDynamicPoints();

        $result = $this->service->submitFlag($challenge, $user, 'SLAU_CSIC{flag}');

        expect($result['success'])->toBeTrue();
        expect($result['points'])->toBe($expectedPoints);

        $this->assertDatabaseHas('ctf_submissions', [
            'ctf_challenge_id' => $challenge->id,
            'user_id' => $user->id,
            'points_awarded' => $expectedPoints,
        ]);
    });

    it('defaults min_points to 50% of max when not set', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'points' => 400,
            'min_points' => null,
            'decay_factor' => 20,
            'dynamic_scoring' => true,
            'solve_count' => 100,
        ]);

        $points = $challenge->getDynamicPoints();
        // min_points defaults to 200 (50% of 400)
        expect($points)->toBeGreaterThanOrEqual(200);
    });
});

// ─── Competition Time Enforcement Tests ───

describe('Competition Time Enforcement', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->user = User::factory()->create();
    });

    it('allows submission on active competition', function () {
        $competition = createCompetition();
        $challenge = createChallenge($competition);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeTrue();
    });

    it('rejects submission on expired competition', function () {
        $competition = createCompetition([
            'start_date' => now()->subDays(14),
            'end_date' => now()->subDays(7),
        ]);
        $challenge = createChallenge($competition);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('competition_inactive');
    });

    it('rejects submission on upcoming competition', function () {
        $competition = createCompetition([
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(14),
        ]);
        $challenge = createChallenge($competition);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('competition_inactive');
    });

    it('rejects submission on draft competition', function () {
        $competition = createCompetition(['status' => 'draft']);
        $challenge = createChallenge($competition);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('competition_inactive');
    });

    it('allows submission on competition with no end date', function () {
        $competition = createCompetition([
            'start_date' => now()->subDay(),
            'end_date' => null,
        ]);
        $challenge = createChallenge($competition);

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeTrue();
    });

    it('blocks web submission on inactive competition', function () {
        $competition = createCompetition([
            'start_date' => now()->subDays(14),
            'end_date' => now()->subDays(7),
        ]);
        $challenge = createChallenge($competition);

        $this->actingAs($this->user)
            ->post(route('ctf.submit', [$competition, $challenge]), [
                'flag' => 'SLAU_CSIC{test_flag}',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'This competition is not currently active');
    });
});

// ─── Max Attempts Tests ───

describe('Max Attempts', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->competition = createCompetition();
        $this->user = User::factory()->create();
    });

    it('enforces max_attempts limit', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'max_attempts' => 2,
        ]);

        $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{wrong1}');
        $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{wrong2}');

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('max_attempts_reached');
    });

    it('allows unlimited attempts when max_attempts is 0', function () {
        $challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}', [
            'max_attempts' => 0,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $result = $this->service->submitFlag($challenge, $this->user, "SLAU_CSIC{wrong{$i}}");
            expect($result['success'])->toBeFalse();
        }

        $result = $this->service->submitFlag($challenge, $this->user, 'SLAU_CSIC{flag}');
        expect($result['success'])->toBeTrue();
    });
});

// ─── Scoreboard Team Tests ───

describe('Team Scoreboard', function () {
    beforeEach(function () {
        $this->service = app(CtfService::class);
        $this->scoreboardService = app(\App\Services\CtfScoreboardService::class);
        $this->competition = createCompetition(['allow_teams' => true]);
        $this->challenge = createChallenge($this->competition, 'SLAU_CSIC{flag}');
    });

    it('returns team scoreboard ranked by score', function () {
        $captain1 = User::factory()->create(['name' => 'Alice']);
        $captain2 = User::factory()->create(['name' => 'Bob']);
        $member1 = User::factory()->create();

        $team1 = $this->service->createTeam($this->competition, $captain1, 'Alpha');
        $team2 = $this->service->createTeam($this->competition, $captain2, 'Bravo');

        $this->service->joinTeam($team1, $member1);

        $this->service->submitFlag($this->challenge, $captain1, 'SLAU_CSIC{flag}', null, $team1);
        $this->service->submitFlag($this->challenge, $member1, 'SLAU_CSIC{flag}', null, $team1);
        $this->service->submitFlag($this->challenge, $captain2, 'SLAU_CSIC{flag}', null, $team2);

        $scoreboard = $this->scoreboardService->getTeamScoreboard($this->competition);

        expect($scoreboard)->toHaveCount(2);
        expect($scoreboard[0]['name'])->toBe('Alpha');
        expect($scoreboard[0]['member_count'])->toBe(2);
        expect($scoreboard[1]['name'])->toBe('Bravo');
    });

    it('returns correct user rank in team mode', function () {
        $captain = User::factory()->create();
        $team = $this->service->createTeam($this->competition, $captain, 'Alpha');

        $this->service->submitFlag($this->challenge, $captain, 'SLAU_CSIC{flag}', null, $team);

        $rank = $this->scoreboardService->getUserRank($this->competition, $captain);

        expect($rank)->toBe(1);
    });

    it('returns null rank for user not in any team', function () {
        $user = User::factory()->create();

        $rank = $this->scoreboardService->getUserRank($this->competition, $user);

        expect($rank)->toBeNull();
    });
});
