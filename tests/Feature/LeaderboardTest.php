<?php

use App\Models\PointTransaction;
use App\Models\User;
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

    PointTransaction::where('user_id', $alice->id)->update(['created_at' => now()->subDays(2)]);

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
