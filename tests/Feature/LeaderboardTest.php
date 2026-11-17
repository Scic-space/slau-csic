<?php

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Meeting;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\GamificationRebuildService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ], $overrides));
}

it('renders the public leaderboard page', function () {
    $this->get(route('leaderboard.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('public/Leaderboard'));
});

it('shows empty state when no points exist', function () {
    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page->where('leaders', [])->where('period', 'all-time'));
});

it('displays users ranked by total points', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create(['user_id' => $alice->id, 'points' => 200, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $bob->id, 'points' => 500, 'reason' => 'Test']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page
            ->where('leaders.0.name', 'Bob')
            ->where('leaders.0.total_points', 500)
            ->where('leaders.0.rank', 1)
            ->where('leaders.1.name', 'Alice')
            ->where('leaders.1.total_points', 200)
            ->where('leaders.1.rank', 2));
});

it('limits results to 50 users', function () {
    $users = collect(range(1, 55))->map(fn ($i) => createUser());

    $users->each(function ($user, $i) {
        PointTransaction::create([
            'user_id' => $user->id,
            'points' => 55 - $i,
            'reason' => 'Test',
        ]);
    });

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page->has('leaders', 50));
});

it('filters by month period', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create([
        'user_id' => $alice->id,
        'points' => 300,
        'reason' => 'Test',
    ]);

    PointTransaction::where('id', '!=', 0)->update(['created_at' => now()->subDays(5)]);

    PointTransaction::create([
        'user_id' => $bob->id,
        'points' => 500,
        'reason' => 'Test',
    ]);

    PointTransaction::where('user_id', $bob->id)->update(['created_at' => now()->subMonths(2)]);

    $this->get(route('leaderboard.index').'?period=month')
        ->assertInertia(fn ($page) => $page
            ->where('leaders.0.name', 'Alice')
            ->where('period', 'month')
            ->has('leaders', 1));
});

it('filters by week period', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create([
        'user_id' => $alice->id,
        'points' => 100,
        'reason' => 'Test',
    ]);

    PointTransaction::where('user_id', $alice->id)->update(['created_at' => now()->startOfWeek()->addHours(6)]);

    PointTransaction::create([
        'user_id' => $bob->id,
        'points' => 200,
        'reason' => 'Test',
    ]);

    PointTransaction::where('user_id', $bob->id)->update(['created_at' => now()->subWeeks(3)]);

    $this->get(route('leaderboard.index').'?period=week')
        ->assertInertia(fn ($page) => $page
            ->where('leaders.0.name', 'Alice')
            ->where('period', 'week')
            ->has('leaders', 1));
});

it('shows current user rank callout', function () {
    $user = createUser();
    $this->actingAs($user);

    PointTransaction::create(['user_id' => $user->id, 'points' => 150, 'reason' => 'Test']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page
            ->where('currentUserRank.rank', 1)
            ->where('currentUserRank.points', 150));
});

it('does not show rank callout for guests', function () {
    createUser();

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page->where('currentUserRank', null));
});

it('excludes negative point totals from leaderboard', function () {
    $user = createUser(['name' => 'Alice']);

    PointTransaction::create(['user_id' => $user->id, 'points' => 100, 'reason' => 'Earned']);
    PointTransaction::create(['user_id' => $user->id, 'points' => -200, 'reason' => 'Deducted']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page->has('leaders', 0));
});

it('correctly ranks users with same points', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create(['user_id' => $alice->id, 'points' => 100, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $bob->id, 'points' => 100, 'reason' => 'Test']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page->has('leaders', 2));
});

it('only shows active members', function () {
    $active = createUser(['name' => 'Active']);
    $inactive = createUser(['name' => 'Inactive', 'membership_status' => 'inactive']);

    PointTransaction::create(['user_id' => $active->id, 'points' => 100, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $inactive->id, 'points' => 200, 'reason' => 'Test']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page
            ->has('leaders', 1)
            ->where('leaders.0.name', 'Active'));
});

it('shows user rank as position among all users', function () {
    $user = createUser();
    $this->actingAs($user);

    collect(range(1, 3))->each(fn () => createUser());

    User::where('id', '!=', $user->id)
        ->where('membership_status', 'active')
        ->each(function ($u) {
            PointTransaction::create(['user_id' => $u->id, 'points' => 50, 'reason' => 'Test']);
        });

    PointTransaction::create(['user_id' => $user->id, 'points' => 50, 'reason' => 'Test']);

    $this->get(route('leaderboard.index'))
        ->assertInertia(fn ($page) => $page
            ->where('currentUserRank.rank', 1)
            ->where('totalMembers', 4));
});

function rebuildableMember(): User
{
    $user = createUser();
    $meeting = Meeting::factory()->teachingSession()->create([
        'scheduled_at' => now()->subDay(),
        'started_at' => now()->subDay(),
        'ended_at' => now()->subDay()->addHour(),
        'attendance_open' => false,
        'late_threshold_minutes' => 15,
    ]);
    Attendance::create([
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subDay()->addMinutes(5),
    ]);

    $event = Event::factory()->create(['status' => 'completed']);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'registered',
        'attended_at' => now()->subDay(),
    ]);

    PointTransaction::create([
        'user_id' => $user->id,
        'points' => 10,
        'reason' => 'Registered for event: Old rules',
        'reference_type' => Event::class,
        'reference_id' => $event->id,
    ]);

    return $user;
}

it('previews a rebuild without writing in dry-run mode', function () {
    $user = rebuildableMember();

    $report = app(GamificationRebuildService::class)->rebuild($user, true);

    expect($report['before'])->toBe(10)
        ->and($report['removed_points'])->toBe(10)
        ->and($report['added_points'])->toBe(70)
        ->and($report['after'])->toBe(70)
        ->and($user->fresh()->total_points)->toBe(10)
        ->and(PointTransaction::where('user_id', $user->id)->count())->toBe(1);
});

it('rebuilds a ledger from records and syncs counters and streaks', function () {
    $user = rebuildableMember();

    $report = app(GamificationRebuildService::class)->rebuild($user);

    expect($report['after'])->toBe(70);

    $user->refresh();

    expect($user->total_points)->toBe(70)
        ->and($user->attendance_count)->toBe(1)
        ->and($user->total_sessions_attended)->toBe(1)
        ->and($user->current_streak)->toBe(1)
        ->and($user->longest_streak)->toBe(1)
        ->and($user->rank)->toBe('bronze');

    expect(PointTransaction::where('user_id', $user->id)->where('reason', 'like', 'Registered for event:%')->count())->toBe(0);
});

it('runs the rebuild command in dry-run mode', function () {
    rebuildableMember();

    $this->artisan('gamification:rebuild', ['--dry-run' => true])->assertSuccessful();

    expect(PointTransaction::sum('points'))->toBe(10);
});
