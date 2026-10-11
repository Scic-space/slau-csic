<?php

use App\Models\Attendance;
use App\Models\ClubResource;
use App\Models\ClubResourceProgress;
use App\Models\Meeting;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the dashboard to authenticated verified members', function () {
    $user = User::factory()->create();
    ClubResource::factory()->create([
        'category' => 'ctf',
        'title' => 'HTB Track',
        'slug' => 'htb-track',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
});

it('shows section pages for competitions and classes', function () {
    $user = User::factory()->create();
    ClubResource::factory()->create([
        'category' => 'competition',
        'title' => 'Internal Sprint',
        'slug' => 'internal-sprint',
    ]);
    ClubResource::factory()->create([
        'category' => 'class',
        'title' => 'Secure Coding Class',
        'slug' => 'secure-coding-class',
    ]);

    $this->actingAs($user)->get(route('portal.competitions'))
        ->assertSuccessful()
        ->assertSee('Internal Competitions');

    $this->actingAs($user)->get(route('portal.classes'))
        ->assertSuccessful()
        ->assertSee('Internal Online Classes');
});

it('stores member progress for club resources', function () {
    $user = User::factory()->create();
    $resource = ClubResource::factory()->create([
        'category' => 'ctf',
        'title' => 'Pico Practice',
        'slug' => 'pico-practice',
    ]);

    $response = $this->actingAs($user)->post(route('portal.progress.update', $resource), [
        'status' => 'in_progress',
        'progress_percentage' => 55,
        'completed_units' => 4,
        'score' => 120,
        'ranking' => 'Top 10',
        'notes' => 'Completed the first web and crypto modules.',
    ]);

    $response->assertRedirect();

    expect(ClubResourceProgress::query()->where('user_id', $user->id)->where('club_resource_id', $resource->id)->exists())->toBeTrue();

    $progress = ClubResourceProgress::query()->where('user_id', $user->id)->where('club_resource_id', $resource->id)->first();

    expect($progress->progress_percentage)->toBe(55)
        ->and($progress->ranking)->toBe('Top 10')
        ->and($progress->status)->toBe('in_progress');
});

it('shows past attended classes on the internal classes page', function () {
    $user = User::factory()->create();
    $attended = Meeting::factory()->teachingSession()->create([
        'title' => 'Attended Bash Class',
        'scheduled_at' => now()->subWeek(),
    ]);
    Attendance::create([
        'meeting_id' => $attended->id,
        'user_id' => $user->id,
        'status' => 'present',
        'checked_in_at' => now()->subWeek(),
    ]);
    $missed = Meeting::factory()->teachingSession()->create([
        'title' => 'Missed Python Class',
        'scheduled_at' => now()->subWeek(),
    ]);

    $this->actingAs($user)
        ->get(route('portal.classes'))
        ->assertOk()
        ->assertSee('Attended Bash Class')
        ->assertSee(route('classes.show', $attended))
        ->assertDontSee('Missed Python Class');
});

it('awards learning-track points only once when a track is completed', function () {
    $user = User::factory()->create();
    $resource = ClubResource::factory()->create([
        'category' => 'class',
        'title' => 'Secure Coding Track',
        'slug' => 'secure-coding-track',
    ]);

    $payload = [
        'status' => 'completed',
        'progress_percentage' => 100,
        'completed_units' => 8,
        'notes' => 'Finished every module.',
    ];

    $this->actingAs($user)->post(route('portal.progress.update', $resource), $payload)->assertRedirect();
    $this->actingAs($user)->post(route('portal.progress.update', $resource), $payload)->assertRedirect();

    expect(PointTransaction::where('user_id', $user->id)->count())->toBe(1)
        ->and(PointTransaction::where('user_id', $user->id)->sum('points'))->toBe(GamificationService::POINTS_TRACK_COMPLETED);
});
