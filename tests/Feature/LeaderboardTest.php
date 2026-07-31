<?php

use App\Livewire\Leaderboard;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ], $overrides));
}

it('renders the leaderboard page', function () {
    $this->get(route('leaderboard.index'))
        ->assertOk()
        ->assertSeeLivewire(Leaderboard::class);
});

it('shows empty state when no points exist', function () {
    Livewire::test(Leaderboard::class)
        ->assertSee('No leaderboard data yet');
});

it('displays users ranked by total points', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create(['user_id' => $alice->id, 'points' => 200, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $bob->id, 'points' => 500, 'reason' => 'Test']);

    Livewire::test(Leaderboard::class)
        ->assertDontSee('No leaderboard data yet')
        ->assertSee('Bob')
        ->assertSee('Alice')
        ->assertSee('500')
        ->assertSee('200');
});

it('shows top 3 podium cards', function () {
    $users = collect(range(1, 3))->map(fn ($i) => createUser());

    $users->each(function ($user, $i) {
        PointTransaction::create([
            'user_id' => $user->id,
            'points' => (3 - $i) * 100,
            'reason' => 'Test',
        ]);
    });

    Livewire::test(Leaderboard::class)
        ->assertSee($users->pluck('name')->toArray());
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

    Livewire::test(Leaderboard::class)
        ->assertSee($users->first()->name)
        ->assertDontSee($users->last()->name);
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

    Livewire::test(Leaderboard::class)
        ->set('period', 'month')
        ->assertSee('Alice')
        ->assertDontSee('Bob');
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

    Livewire::test(Leaderboard::class)
        ->set('period', 'week')
        ->assertSee('Alice')
        ->assertDontSee('Bob');
});

it('shows current user rank callout', function () {
    $user = createUser();
    $this->actingAs($user);

    PointTransaction::create(['user_id' => $user->id, 'points' => 150, 'reason' => 'Test']);

    Livewire::test(Leaderboard::class)
        ->assertSee("You're ranked", escape: false)
        ->assertSee('150');
});

it('hides rank callout for users with no points', function () {
    $user = createUser();
    $this->actingAs($user);

    Livewire::test(Leaderboard::class)
        ->assertDontSee("You're ranked", escape: false);
});

it('does not show rank callout for guests', function () {
    createUser();

    Livewire::test(Leaderboard::class)
        ->assertDontSee("You're ranked", escape: false);
});

it('excludes negative point totals from leaderboard', function () {
    $user = createUser(['name' => 'Alice']);

    PointTransaction::create(['user_id' => $user->id, 'points' => 100, 'reason' => 'Earned']);
    PointTransaction::create(['user_id' => $user->id, 'points' => -200, 'reason' => 'Deducted']);

    Livewire::test(Leaderboard::class)
        ->assertDontSee('Alice');
});

it('correctly ranks users with same points', function () {
    $alice = createUser(['name' => 'Alice']);
    $bob = createUser(['name' => 'Bob']);

    PointTransaction::create(['user_id' => $alice->id, 'points' => 100, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $bob->id, 'points' => 100, 'reason' => 'Test']);

    Livewire::test(Leaderboard::class)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

it('only shows active members', function () {
    $active = createUser(['name' => 'Active']);
    $inactive = createUser(['name' => 'Inactive', 'membership_status' => 'inactive']);

    PointTransaction::create(['user_id' => $active->id, 'points' => 100, 'reason' => 'Test']);
    PointTransaction::create(['user_id' => $inactive->id, 'points' => 200, 'reason' => 'Test']);

    Livewire::test(Leaderboard::class)
        ->assertSee('Active')
        ->assertDontSee('Inactive');
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

    Livewire::test(Leaderboard::class)
        ->assertSee("You're ranked", escape: false)
        ->assertSee('of 4 members');
});
