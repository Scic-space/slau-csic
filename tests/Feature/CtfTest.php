<?php

use App\Models\Badge;
use App\Models\CtfCategory;
use App\Models\CtfChallenge;
use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\CtfWriteup;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\CtfScoreboardService;
use App\Services\CtfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// ─── Helpers ───

function createAdmin(): User
{
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

function activeCompetition(): CtfCompetition
{
    return CtfCompetition::factory()->create();
}

function activeChallenge(CtfCompetition $competition, string $flag = 'SLAU_CSIC{test_flag}'): CtfChallenge
{
    return CtfChallenge::factory()
        ->withFlag($flag)
        ->create(['ctf_competition_id' => $competition->id]);
}

// ─── CtfService ───

describe('CtfService::submitFlag', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
        $this->challenge = activeChallenge($this->competition);
    });

    it('returns success for a correct flag', function () {
        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeTrue();
        expect($result['points'])->toBe($this->challenge->points);
    });

    it('records a correct submission in the database', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $this->assertDatabaseHas('ctf_submissions', [
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $this->user->id,
            'is_correct' => true,
            'points_awarded' => $this->challenge->points,
        ]);
    });

    it('returns error for an incorrect flag', function () {
        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('incorrect');
    });

    it('records an incorrect submission in the database', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong_flag}');

        $this->assertDatabaseHas('ctf_submissions', [
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $this->user->id,
            'is_correct' => false,
            'points_awarded' => 0,
        ]);
    });

    it('returns already_solved for duplicate correct submission', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('already_solved');
    });

    it('allows multiple incorrect attempts on the same challenge', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong1}');
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong2}');
        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong3}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('incorrect');
    });

    it('awards points via GamificationService on correct flag', function () {
        $initialPoints = $this->user->total_points;

        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($this->user->fresh()->total_points)->toBe($initialPoints + $this->challenge->points);
    });

    it('increments attempt number on each submission', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong1}');
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{wrong2}');
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $submissions = CtfSubmission::where('ctf_challenge_id', $this->challenge->id)
            ->where('user_id', $this->user->id)
            ->orderBy('attempt_number')
            ->get();

        expect($submissions[0]->attempt_number)->toBe(1);
        expect($submissions[1]->attempt_number)->toBe(2);
        expect($submissions[2]->attempt_number)->toBe(3);
    });

    it('stores IP address when provided', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}', '192.168.1.1');

        $this->assertDatabaseHas('ctf_submissions', [
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.1',
        ]);
    });

    it('awards badges when CTF scoring milestone is reached', function () {
        $badge = Badge::factory()->create([
            'criteria_type' => 'ctf_score',
            'criteria_value' => $this->challenge->points,
        ]);

        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeTrue();
        expect(UserBadge::where('user_id', $this->user->id)->where('badge_id', $badge->id)->exists())->toBeTrue();
    });

    it('is idempotent for already solved flag within the return value', function () {
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $result = app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('already_solved');
    });
});

describe('CtfService::createChallenge', function () {
    beforeEach(function () {
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
    });

    it('creates a challenge with valid flag format', function () {
        $challenge = app(CtfService::class)->createChallenge([
            'ctf_competition_id' => $this->competition->id,
            'ctf_category_id' => $this->category->id,
            'title' => 'Test Challenge',
            'description' => 'A test challenge',
            'flag' => 'SLAU_CSIC{valid_flag}',
            'points' => 100,
            'difficulty' => 'easy',
        ]);

        expect($challenge)->toBeInstanceOf(CtfChallenge::class);
        expect(Hash::check(strtolower('SLAU_CSIC{valid_flag}'), $challenge->flag_hash))->toBeTrue();
        expect(Hash::check(strtolower('SLAU_CSIC{valid_flag}'), $challenge->fresh()->flag_hash))->toBeTrue();
    });

    it('throws exception for invalid flag format', function () {
        app(CtfService::class)->createChallenge([
            'ctf_competition_id' => $this->competition->id,
            'ctf_category_id' => $this->category->id,
            'title' => 'Test Challenge',
            'flag' => 'invalid-flag',
            'points' => 100,
            'difficulty' => 'easy',
        ]);
    })->throws(InvalidArgumentException::class, 'Flag must match format: SLAU_CSIC{text}');

    it('throws exception for missing flag', function () {
        app(CtfService::class)->createChallenge([
            'ctf_competition_id' => $this->competition->id,
            'ctf_category_id' => $this->category->id,
            'title' => 'Test Challenge',
            'points' => 100,
            'difficulty' => 'easy',
        ]);
    })->throws(InvalidArgumentException::class);

    it('does not store the raw flag in the database', function () {
        $challenge = app(CtfService::class)->createChallenge([
            'ctf_competition_id' => $this->competition->id,
            'ctf_category_id' => $this->category->id,
            'title' => 'Hidden Flag Challenge',
            'flag' => 'SLAU_CSIC{secret_hidden}',
            'points' => 200,
            'difficulty' => 'medium',
        ]);

        $fresh = $challenge->fresh();
        expect(Hash::check(strtolower('SLAU_CSIC{secret_hidden}'), $fresh->flag_hash))->toBeTrue();
        expect($fresh->getAttributes())->not->toHaveKey('flag');
    });
});

describe('CtfService::updateChallenge', function () {
    beforeEach(function () {
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
        $this->challenge = app(CtfService::class)->createChallenge([
            'ctf_competition_id' => $this->competition->id,
            'ctf_category_id' => $this->category->id,
            'title' => 'Original Challenge',
            'flag' => 'SLAU_CSIC{original_flag}',
            'points' => 100,
            'difficulty' => 'easy',
        ]);
    });

    it('updates challenge fields', function () {
        $updated = app(CtfService::class)->updateChallenge($this->challenge, [
            'title' => 'Updated Challenge',
            'points' => 200,
        ]);

        expect($updated->fresh()->title)->toBe('Updated Challenge');
        expect($updated->fresh()->points)->toBe(200);
    });

    it('updates flag when provided', function () {
        $updated = app(CtfService::class)->updateChallenge($this->challenge, [
            'flag' => 'SLAU_CSIC{new_flag}',
        ]);

        expect(Hash::check(strtolower('SLAU_CSIC{new_flag}'), $updated->fresh()->flag_hash))->toBeTrue();
    });

    it('throws exception for invalid flag format on update', function () {
        app(CtfService::class)->updateChallenge($this->challenge, [
            'flag' => 'bad-flag',
        ]);
    })->throws(InvalidArgumentException::class, 'Flag must match format: SLAU_CSIC{text}');
});

describe('CtfService::getUserSolves', function () {
    it('returns solved challenges for user', function () {
        $user = User::factory()->create();
        $competition = activeCompetition();
        $challenge = activeChallenge($competition);

        app(CtfService::class)->submitFlag($challenge, $user, 'SLAU_CSIC{test_flag}');

        $solves = app(CtfService::class)->getUserSolves($competition, $user);

        expect($solves)->toHaveCount(1);
        expect($solves->first()->challenge->id)->toBe($challenge->id);
    });

    it('returns empty collection when user has no solves', function () {
        $user = User::factory()->create();
        $competition = activeCompetition();

        $solves = app(CtfService::class)->getUserSolves($competition, $user);

        expect($solves)->toHaveCount(0);
    });
});

describe('CtfService::getAttemptNumber', function () {
    it('returns 1 on first attempt', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge(activeCompetition());

        $attempt = app(CtfService::class)->getAttemptNumber($challenge, $user);

        expect($attempt)->toBe(1);
    });

    it('increments after each submission', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge(activeCompetition());

        app(CtfService::class)->submitFlag($challenge, $user, 'SLAU_CSIC{wrong1}');
        app(CtfService::class)->submitFlag($challenge, $user, 'SLAU_CSIC{wrong2}');

        $attempt = app(CtfService::class)->getAttemptNumber($challenge, $user);

        expect($attempt)->toBe(3);
    });
});

// ─── CtfScoreboardService ───

describe('CtfScoreboardService', function () {
    beforeEach(function () {
        $this->service = app(CtfScoreboardService::class);
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
        $this->challenge1 = activeChallenge($this->competition, 'SLAU_CSIC{flag1}');
        $this->challenge2 = activeChallenge($this->competition, 'SLAU_CSIC{flag2}');
        $this->user1 = User::factory()->create(['name' => 'Alice']);
        $this->user2 = User::factory()->create(['name' => 'Bob']);
    });

    it('returns empty scoreboard when no solves', function () {
        $scoreboard = $this->service->getScoreboard($this->competition);

        expect($scoreboard)->toHaveCount(0);
    });

    it('returns users ranked by total score descending', function () {
        app(CtfService::class)->submitFlag($this->challenge1, $this->user1, 'SLAU_CSIC{flag1}');
        app(CtfService::class)->submitFlag($this->challenge1, $this->user2, 'SLAU_CSIC{flag1}');
        app(CtfService::class)->submitFlag($this->challenge2, $this->user1, 'SLAU_CSIC{flag2}');

        $scoreboard = $this->service->getScoreboard($this->competition);

        expect($scoreboard)->toHaveCount(2);
        expect($scoreboard[0]['name'])->toBe('Alice');
        expect($scoreboard[0]['total_score'])->toBe(
            $this->challenge1->points + $this->challenge2->points
        );
        expect($scoreboard[1]['name'])->toBe('Bob');
        expect($scoreboard[1]['total_score'])->toBe($this->challenge1->points);
    });

    it('caches scoreboard results', function () {
        app(CtfService::class)->submitFlag($this->challenge1, $this->user1, 'SLAU_CSIC{flag1}');

        $first = $this->service->getScoreboard($this->competition);
        expect($first)->toHaveCount(1);

        app(CtfService::class)->submitFlag($this->challenge2, $this->user1, 'SLAU_CSIC{flag2}');

        // Should still return cached result (1 user) before TTL expiry
        $second = $this->service->getScoreboard($this->competition);
        expect($second)->toHaveCount(1);
    });

    it('returns correct user rank', function () {
        app(CtfService::class)->submitFlag($this->challenge1, $this->user1, 'SLAU_CSIC{flag1}');
        app(CtfService::class)->submitFlag($this->challenge1, $this->user2, 'SLAU_CSIC{flag1}');

        $rank = $this->service->getUserRank($this->competition, $this->user1);

        expect($rank)->toBe(1);
    });

    it('returns null rank for user with no solves', function () {
        $rank = $this->service->getUserRank($this->competition, $this->user1);

        expect($rank)->toBeNull();
    });

    it('invalidates cache', function () {
        app(CtfService::class)->submitFlag($this->challenge1, $this->user1, 'SLAU_CSIC{flag1}');
        $this->service->getScoreboard($this->competition);

        $this->service->invalidateCache($this->competition);
        app(CtfService::class)->submitFlag($this->challenge2, $this->user1, 'SLAU_CSIC{flag2}');

        $fresh = $this->service->getScoreboard($this->competition);
        expect($fresh->first()['total_score'])->toBe(
            $this->challenge1->points + $this->challenge2->points
        );
    });

    it('returns solves count for each user', function () {
        app(CtfService::class)->submitFlag($this->challenge1, $this->user1, 'SLAU_CSIC{flag1}');
        app(CtfService::class)->submitFlag($this->challenge2, $this->user1, 'SLAU_CSIC{flag2}');

        $scoreboard = $this->service->getScoreboard($this->competition);

        expect($scoreboard[0]['solves_count'])->toBe(2);
    });
});

// ─── CtfController ───

describe('CtfController (Web)', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
    });

    it('shows the scoreboard page for a competition', function () {
        $this->actingAs($this->user)
            ->get(route('ctf.scoreboard', $this->competition))
            ->assertSuccessful()
            ->assertSee('Scoreboard');
    });

    it('shows 403 for non-published competition on show page', function () {
        $draft = CtfCompetition::factory()->draft()->create();

        $this->actingAs($this->user)
            ->get(route('ctf.competition', $draft))
            ->assertForbidden();
    });

    it('allows admin to view draft competition', function () {
        $admin = createAdmin();
        $draft = CtfCompetition::factory()->draft()->create();

        $this->actingAs($admin)
            ->get(route('ctf.competition', $draft))
            ->assertSuccessful();
    });

    it('shows the writeup page for a solved challenge', function () {
        $challenge = activeChallenge($this->competition);
        app(CtfService::class)->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $this->actingAs($this->user)
            ->get(route('ctf.writeup', [$this->competition, $challenge]))
            ->assertSuccessful();
    });

    it('returns 403 for writeup page on unsolved challenge', function () {
        $challenge = activeChallenge($this->competition);

        $this->actingAs($this->user)
            ->get(route('ctf.writeup', [$this->competition, $challenge]))
            ->assertForbidden();
    });

    it('submits a writeup for a solved challenge', function () {
        $challenge = activeChallenge($this->competition);
        app(CtfService::class)->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $this->actingAs($this->user)
            ->post(route('ctf.writeup.submit', [$this->competition, $challenge]), [
                'content' => fake()->paragraphs(3, true),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ctf_writeups', [
            'ctf_challenge_id' => $challenge->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    });

    it('rejects writeup with too short content', function () {
        $challenge = activeChallenge($this->competition);
        app(CtfService::class)->submitFlag($challenge, $this->user, 'SLAU_CSIC{test_flag}');

        $this->actingAs($this->user)
            ->post(route('ctf.writeup.submit', [$this->competition, $challenge]), [
                'content' => 'Too short',
            ])
            ->assertSessionHasErrors('content');
    });

    it('redirects to login for unauthenticated scoreboard access', function () {
        $this->get(route('ctf.scoreboard', $this->competition))
            ->assertRedirect(route('auth.login'));
    });
});

// ─── CtfApiController ───

describe('CtfApiController', function () {
    beforeEach(function () {
        $this->competition = activeCompetition();
        $this->category = CtfCategory::factory()->create();
    });

    it('returns competition show with challenges', function () {
        $challenge = activeChallenge($this->competition);

        $this->getJson("/api/ctf/competitions/{$this->competition->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $this->competition->id);
    });

    it('returns 404 for non-active competition', function () {
        $draft = CtfCompetition::factory()->draft()->create();

        $this->getJson("/api/ctf/competitions/{$draft->id}")
            ->assertNotFound();
    });

    it('requires authentication to submit flag', function () {
        $challenge = activeChallenge($this->competition);

        $this->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
            'flag' => 'SLAU_CSIC{test_flag}',
        ])->assertUnauthorized();
    });

    it('accepts correct flag via API', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge($this->competition);

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
                'flag' => 'SLAU_CSIC{test_flag}',
            ])
            ->assertOk()
            ->assertJson(['correct' => true, 'success' => true]);
    });

    it('rejects incorrect flag via API', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge($this->competition);

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
                'flag' => 'SLAU_CSIC{wrong}',
            ])
            ->assertOk()
            ->assertJson(['correct' => false, 'success' => false]);
    });

    it('returns 409 for already solved flag via API', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge($this->competition);

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
                'flag' => 'SLAU_CSIC{test_flag}',
            ])
            ->assertOk();

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
                'flag' => 'SLAU_CSIC{test_flag}',
            ])
            ->assertStatus(409);
    });

    it('rejects invalid flag format via API', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge($this->competition);

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [
                'flag' => 'invalid',
            ])
            ->assertUnprocessable();
    });

    it('returns the scoreboard via API', function () {
        $user = User::factory()->create(['name' => 'Alice']);
        $challenge = activeChallenge($this->competition);
        app(CtfService::class)->submitFlag($challenge, $user, 'SLAU_CSIC{test_flag}');

        $this->getJson("/api/ctf/competitions/{$this->competition->id}/scoreboard")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.0.solves_count', 1);
    });

    it('returns 422 for missing flag field via API', function () {
        $user = User::factory()->create();
        $challenge = activeChallenge($this->competition);

        $this->actingAs($user)
            ->postJson("/api/ctf/challenges/{$challenge->id}/submit", [])
            ->assertUnprocessable();
    });
});

// ─── CtfWriteup ───

describe('CtfWriteup', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->reviewer = createAdmin();
        $this->competition = activeCompetition();
        $this->challenge = activeChallenge($this->competition);
        app(CtfService::class)->submitFlag($this->challenge, $this->user, 'SLAU_CSIC{test_flag}');
        $this->writeup = CtfWriteup::factory()->create([
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $this->user->id,
        ]);
    });

    it('checks challenge is accessible', function () {
        expect(CtfChallenge::find($this->challenge->id))->not->toBeNull();
        expect($this->writeup->getAttribute('ctf_challenge_id'))->toBe($this->challenge->id);

        $freshWriteup = CtfWriteup::find($this->writeup->id);
        expect($freshWriteup->challenge->id)->toBe($this->challenge->id);
    });

    it('can be approved by an admin', function () {
        $this->writeup->approve($this->reviewer);

        expect($this->writeup->fresh()->status)->toBe('approved');
        expect($this->writeup->fresh()->reviewed_by)->toBe($this->reviewer->id);
        expect($this->writeup->fresh()->reviewed_at)->not->toBeNull();
    });

    it('can be rejected by an admin', function () {
        $this->writeup->reject($this->reviewer);

        expect($this->writeup->fresh()->status)->toBe('rejected');
        expect($this->writeup->fresh()->reviewed_by)->toBe($this->reviewer->id);
        expect($this->writeup->fresh()->reviewed_at)->not->toBeNull();
    });

    it('scopes pending writeups correctly', function () {
        $approved = CtfWriteup::factory()->approved()->create([
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => User::factory(),
        ]);

        $pending = CtfWriteup::pending()->get();

        expect($pending->where('id', $this->writeup->id))->toHaveCount(1);
        expect($pending->where('id', $approved->id))->toHaveCount(0);
    });

    it('belongs to a user', function () {
        expect($this->writeup->user->id)->toBe($this->user->id);
    });

    it('belongs to a challenge', function () {
        expect($this->writeup->challenge->id)->toBe($this->challenge->id);
    });

    it('scopes by user', function () {
        $otherUser = User::factory()->create();
        $otherWriteup = CtfWriteup::factory()->create([
            'ctf_challenge_id' => $this->challenge->id,
            'user_id' => $otherUser->id,
        ]);

        $userWriteups = CtfWriteup::byUser($this->user)->get();

        expect($userWriteups)->toHaveCount(1);
        expect($userWriteups->first()->id)->toBe($this->writeup->id);
    });
});

// ─── CtfChallenge Model ───

describe('CtfChallenge', function () {
    beforeEach(function () {
        $this->challenge = activeChallenge(activeCompetition());
    });

    it('verifies correct flag', function () {
        expect($this->challenge->verifyFlag('SLAU_CSIC{test_flag}'))->toBeTrue();
    });

    it('rejects incorrect flag', function () {
        expect($this->challenge->verifyFlag('SLAU_CSIC{wrong}'))->toBeFalse();
    });

    it('rejects flag with wrong format', function () {
        expect($this->challenge->verifyFlag('wrong_flag'))->toBeFalse();
    });

    it('checks if solved by user', function () {
        $user = User::factory()->create();

        expect($this->challenge->isSolvedBy($user))->toBeFalse();

        app(CtfService::class)->submitFlag($this->challenge, $user, 'SLAU_CSIC{test_flag}');

        expect($this->challenge->isSolvedBy($user))->toBeTrue();
    });

    it('returns solve count', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        expect($this->challenge->getSolveCount())->toBe(0);

        app(CtfService::class)->submitFlag($this->challenge, $user1, 'SLAU_CSIC{test_flag}');
        app(CtfService::class)->submitFlag($this->challenge, $user2, 'SLAU_CSIC{test_flag}');

        expect($this->challenge->getSolveCount())->toBe(2);
    });

    it('scopes active challenges', function () {
        $inactive = CtfChallenge::factory()->inactive()->create([
            'ctf_competition_id' => $this->challenge->ctf_competition_id,
        ]);

        $active = CtfChallenge::active()->get();

        expect($active->contains($this->challenge))->toBeTrue();
        expect($active->contains($inactive))->toBeFalse();
    });

    it('auto-generates slug on create', function () {
        $challenge = CtfChallenge::factory()->create([
            'title' => 'My Custom Challenge',
            'slug' => '',
            'ctf_competition_id' => $this->challenge->ctf_competition_id,
        ]);

        expect($challenge->fresh()->slug)->toBe('my-custom-challenge');
    });
});

// ─── CtfCompetition Model ───

describe('CtfCompetition', function () {
    it('scopes published competitions', function () {
        $published = CtfCompetition::factory()->create();
        CtfCompetition::factory()->draft()->create();

        $results = CtfCompetition::published()->get();

        expect($results->contains('id', $published->id))->toBeTrue();
        expect($results)->toHaveCount(1);
    });

    it('scopes public competitions', function () {
        $public = CtfCompetition::factory()->create();
        CtfCompetition::factory()->private()->create();

        $results = CtfCompetition::public()->get();

        expect($results->contains('id', $public->id))->toBeTrue();
        expect($results)->toHaveCount(1);
    });

    it('scopes currently active competitions', function () {
        CtfCompetition::factory()->create();
        CtfCompetition::factory()->upcoming()->create();
        CtfCompetition::factory()->expired()->create();

        $results = CtfCompetition::currentlyActive()->get();

        expect($results)->toHaveCount(1);
    });

    it('checks if competition is active', function () {
        $active = CtfCompetition::factory()->create();
        $upcoming = CtfCompetition::factory()->upcoming()->create();
        $expired = CtfCompetition::factory()->expired()->create();

        expect($active->isActive())->toBeTrue();
        expect($upcoming->isActive())->toBeFalse();
        expect($expired->isActive())->toBeFalse();
    });

    it('counts solved challenges for a user', function () {
        $competition = activeCompetition();
        $category = CtfCategory::factory()->create();
        $challenge1 = activeChallenge($competition, 'SLAU_CSIC{a}');
        $challenge2 = activeChallenge($competition, 'SLAU_CSIC{b}');
        $user = User::factory()->create();

        expect($competition->solvedChallengesCount($user))->toBe(0);

        app(CtfService::class)->submitFlag($challenge1, $user, 'SLAU_CSIC{a}');

        expect($competition->fresh()->solvedChallengesCount($user))->toBe(1);

        app(CtfService::class)->submitFlag($challenge2, $user, 'SLAU_CSIC{b}');

        expect($competition->fresh()->solvedChallengesCount($user))->toBe(2);
    });
});

// ─── CtfSubmission Model ───

describe('CtfSubmission', function () {
    beforeEach(function () {
        $this->submission = CtfSubmission::factory()->create();
    });

    it('has correct scope', function () {
        $incorrect = CtfSubmission::factory()->incorrect()->create();

        $correct = CtfSubmission::correct()->get();

        expect($correct->contains('id', $this->submission->id))->toBeTrue();
        expect($correct->contains('id', $incorrect->id))->toBeFalse();
    });

    it('has incorrect scope', function () {
        $correct = CtfSubmission::factory()->create([
            'is_correct' => false,
        ]);

        $incorrect = CtfSubmission::incorrect()->get();

        expect($incorrect->contains('id', $correct->id))->toBeTrue();
    });

    it('checks isCorrect', function () {
        expect($this->submission->isCorrect())->toBeTrue();

        $incorrect = CtfSubmission::factory()->incorrect()->create();
        expect($incorrect->isCorrect())->toBeFalse();
    });

    it('checks isFirstSolve', function () {
        $challenge = $this->submission->challenge;

        expect($this->submission->isFirstSolve())->toBeTrue();

        // Second solve on same challenge by different user
        $second = CtfSubmission::factory()->create([
            'ctf_challenge_id' => $challenge->id,
            'user_id' => User::factory(),
            'attempt_number' => 1,
            'is_correct' => true,
        ]);

        expect($second->isFirstSolve())->toBeTrue(); // First for this user

        // Third solve on same challenge by same user (shouldn't happen but tests logic)
        $third = CtfSubmission::factory()->create([
            'ctf_challenge_id' => $challenge->id,
            'user_id' => $second->user_id,
            'attempt_number' => 2,
            'is_correct' => true,
        ]);

        expect($third->isFirstSolve())->toBeFalse(); // Not first attempt
    });

    it('belongs to a user', function () {
        expect($this->submission->user)->toBeInstanceOf(User::class);
    });

    it('belongs to a challenge', function () {
        expect($this->submission->challenge)->toBeInstanceOf(CtfChallenge::class);
    });
});

// ─── Factory States ───

describe('CTF Factory States', function () {
    it('creates a draft competition', function () {
        $competition = CtfCompetition::factory()->draft()->create();

        expect($competition->status)->toBe('draft');
    });

    it('creates an archived competition', function () {
        $competition = CtfCompetition::factory()->archived()->create();

        expect($competition->status)->toBe('archived');
    });

    it('creates an upcoming competition', function () {
        $competition = CtfCompetition::factory()->upcoming()->create();

        expect($competition->start_date->isFuture())->toBeTrue();
        expect($competition->end_date->isFuture())->toBeTrue();
    });

    it('creates an expired competition', function () {
        $competition = CtfCompetition::factory()->expired()->create();

        expect($competition->end_date->isPast())->toBeTrue();
    });

    it('creates a private competition', function () {
        $competition = CtfCompetition::factory()->private()->create();

        expect($competition->is_public)->toBeFalse();
    });

    it('creates an inactive challenge', function () {
        $challenge = CtfChallenge::factory()->inactive()->create();

        expect($challenge->is_active)->toBeFalse();
    });

    it('creates an incorrect submission', function () {
        $submission = CtfSubmission::factory()->incorrect()->create();

        expect($submission->is_correct)->toBeFalse();
        expect($submission->points_awarded)->toBe(0);
    });

    it('creates an approved writeup', function () {
        $writeup = CtfWriteup::factory()->approved()->create();

        expect($writeup->status)->toBe('approved');
        expect($writeup->reviewed_by)->not->toBeNull();
    });

    it('creates a rejected writeup', function () {
        $writeup = CtfWriteup::factory()->rejected()->create();

        expect($writeup->status)->toBe('rejected');
        expect($writeup->reviewed_by)->not->toBeNull();
    });
});
