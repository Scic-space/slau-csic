<?php

use App\Models\Competition;
use App\Models\CompetitionParticipants;
use App\Models\User;
use App\Notifications\CompetitionReminderNotification;
use App\Notifications\CompetitionResultsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CompetitionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    // ─── Model Scopes ───────────────────────────────────────────────────

    public function test_upcoming_scope(): void
    {
        Competition::factory()->create(['start_date' => now()->subDays(5), 'end_date' => now()->subDay()]);
        $upcoming = Competition::factory()->create(['start_date' => now()->addWeek(), 'end_date' => now()->addWeeks(2)]);

        $results = Competition::upcoming()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($upcoming->id, $results->first()->id);
    }

    public function test_ongoing_scope(): void
    {
        $ongoing = Competition::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addWeek()]);
        Competition::factory()->create(['start_date' => now()->subWeeks(2), 'end_date' => now()->subWeek()]);

        $results = Competition::ongoing()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($ongoing->id, $results->first()->id);
    }

    public function test_past_scope(): void
    {
        $past = Competition::factory()->create(['start_date' => now()->subWeeks(2), 'end_date' => now()->subWeek()]);
        Competition::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addWeek()]);

        $results = Competition::past()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($past->id, $results->first()->id);
    }

    public function test_search_scope(): void
    {
        $match = Competition::factory()->create(['name' => 'SLAU CTF Championship 2025']);
        Competition::factory()->create(['name' => 'Hackathon Spring 2025']);

        $results = Competition::search('CTF')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($match->id, $results->first()->id);
    }

    public function test_of_type_scope(): void
    {
        $ctf = Competition::factory()->ctf()->create();
        Competition::factory()->hackathon()->create();

        $results = Competition::ofType('ctf')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($ctf->id, $results->first()->id);
    }

    // ─── Model Status Methods ───────────────────────────────────────────

    public function test_is_upcoming(): void
    {
        $competition = Competition::factory()->create(['start_date' => now()->addWeek()]);

        $this->assertTrue($competition->isUpcoming());
        $this->assertFalse($competition->isOngoing());
        $this->assertFalse($competition->isPast());
        $this->assertEquals('Upcoming', $competition->statusLabel());
    }

    public function test_is_ongoing(): void
    {
        $competition = Competition::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
        ]);

        $this->assertTrue($competition->isOngoing());
        $this->assertFalse($competition->isUpcoming());
        $this->assertFalse($competition->isPast());
        $this->assertEquals('Ongoing', $competition->statusLabel());
    }

    public function test_is_past(): void
    {
        $competition = Competition::factory()->create([
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->subWeek(),
        ]);

        $this->assertTrue($competition->isPast());
        $this->assertFalse($competition->isUpcoming());
        $this->assertFalse($competition->isOngoing());
        $this->assertEquals('Past', $competition->statusLabel());
    }

    public function test_user_participation_methods(): void
    {
        $competition = Competition::factory()->create();
        $user = User::factory()->create();

        $this->assertFalse($competition->isUserParticipant($user));

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);

        $competition->refresh();
        $this->assertTrue($competition->isUserParticipant($user));
        $this->assertNotNull($competition->getParticipantForUser($user));
    }

    // ─── Factory States ─────────────────────────────────────────────────

    public function test_factory_ctf_state(): void
    {
        $competition = Competition::factory()->ctf()->create();

        $this->assertEquals('ctf', $competition->type);
        $this->assertTrue($competition->is_team_based);
        $this->assertEquals(5, $competition->max_team_size);
    }

    public function test_factory_completed_state(): void
    {
        $competition = Competition::factory()->completed()->create();

        $this->assertEquals('completed', $competition->participation_status);
        $this->assertTrue($competition->end_date->isPast());
    }

    public function test_factory_ranked_state(): void
    {
        $competition = Competition::factory()->ranked()->create();

        $this->assertNotNull($competition->club_ranking);
        $this->assertNotNull($competition->achievements);
    }

    // ─── Policy Tests ───────────────────────────────────────────────────

    public function test_anyone_can_view_competitions(): void
    {
        $user = User::factory()->create();
        $competition = Competition::factory()->create();

        $this->assertTrue($user->can('viewAny', Competition::class));
        $this->assertTrue($user->can('view', $competition));
    }

    public function test_only_admin_can_create_competitions(): void
    {
        $member = User::factory()->create()->assignRole('member');
        $admin = User::factory()->create()->assignRole('admin');

        $this->assertFalse($member->can('create', Competition::class));
        $this->assertTrue($admin->can('create', Competition::class));
    }

    public function test_only_admin_can_update_competitions(): void
    {
        $member = User::factory()->create()->assignRole('member');
        $admin = User::factory()->create()->assignRole('admin');
        $competition = Competition::factory()->create();

        $this->assertFalse($member->can('update', $competition));
        $this->assertTrue($admin->can('update', $competition));
    }

    public function test_only_admin_can_delete_competitions(): void
    {
        $member = User::factory()->create()->assignRole('member');
        $admin = User::factory()->create()->assignRole('admin');
        $competition = Competition::factory()->create();

        $this->assertFalse($member->can('delete', $competition));
        $this->assertTrue($admin->can('delete', $competition));
    }

    public function test_member_can_join_policy(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        $this->assertTrue($user->can('join', $competition));
    }

    public function test_member_cannot_join_twice_policy(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);

        $competition->refresh();
        $this->assertFalse($user->can('join', $competition));
    }

    public function test_member_can_leave_joined_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);

        $competition->refresh();
        $this->assertTrue($user->can('leave', $competition));
    }

    public function test_member_cannot_leave_unjoined_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        $this->assertFalse($user->can('leave', $competition));
    }

    // ─── Livewire CompetitionListing ────────────────────────────────────

    public function test_competition_listing_shows_competitions(): void
    {
        $competition = Competition::factory()->create(['name' => 'Visible Competition']);

        Livewire::test(\App\Livewire\CompetitionListing::class)
            ->assertSee('Visible Competition')
            ->assertStatus(200);
    }

    public function test_competition_listing_filters_by_type(): void
    {
        Competition::factory()->ctf()->create(['name' => 'CTF Comp']);
        Competition::factory()->hackathon()->create(['name' => 'Hackathon Comp']);

        Livewire::test(\App\Livewire\CompetitionListing::class)
            ->set('type', 'ctf')
            ->assertSee('CTF Comp')
            ->assertDontSee('Hackathon Comp');
    }

    public function test_competition_listing_searches_by_name(): void
    {
        Competition::factory()->create(['name' => 'SLAU CTF Finals']);
        Competition::factory()->create(['name' => 'Coding Challenge']);

        Livewire::test(\App\Livewire\CompetitionListing::class)
            ->set('search', 'SLAU')
            ->assertSee('SLAU CTF Finals')
            ->assertDontSee('Coding Challenge');
    }

    public function test_competition_listing_shows_empty_state(): void
    {
        Livewire::test(\App\Livewire\CompetitionListing::class)
            ->assertSee('No competitions found');
    }

    public function test_competition_listing_clears_filters(): void
    {
        Livewire::test(\App\Livewire\CompetitionListing::class)
            ->set('type', 'ctf')
            ->set('search', 'nonexistent')
            ->call('resetFilters')
            ->assertSet('type', '')
            ->assertSet('search', '')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '');
    }

    // ─── Livewire CompetitionShow ───────────────────────────────────────

    public function test_competition_show_renders(): void
    {
        $competition = Competition::factory()->create(['name' => 'Test Competition']);

        Livewire::test(\App\Livewire\CompetitionShow::class, ['competition' => $competition])
            ->assertSee('Test Competition')
            ->assertStatus(200);
    }

    public function test_member_can_join_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\CompetitionShow::class, ['competition' => $competition])
            ->set('role', 'member')
            ->call('join')
            ->assertSet('showJoinForm', false);

        $this->assertDatabaseHas('competition_participants', [
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function test_member_can_leave_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\CompetitionShow::class, ['competition' => $competition])
            ->call('confirmLeave')
            ->assertSet('showLeaveConfirm', true)
            ->call('leave')
            ->assertSet('showLeaveConfirm', false);

        $this->assertDatabaseMissing('competition_participants', [
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_member_can_cancel_leave(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\CompetitionShow::class, ['competition' => $competition])
            ->call('confirmLeave')
            ->assertSet('showLeaveConfirm', true)
            ->call('cancelLeave')
            ->assertSet('showLeaveConfirm', false);

        $this->assertDatabaseHas('competition_participants', [
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_join_form_toggle(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\CompetitionShow::class, ['competition' => $competition])
            ->assertSet('showJoinForm', false)
            ->call('toggleJoinForm')
            ->assertSet('showJoinForm', true)
            ->call('toggleJoinForm')
            ->assertSet('showJoinForm', false);
    }

    // ─── Notifications ──────────────────────────────────────────────────

    public function test_competition_reminder_notification(): void
    {
        Notification::fake();

        $competition = Competition::factory()->create(['name' => 'Test Comp']);
        $user = User::factory()->create();

        $user->notify(new CompetitionReminderNotification($competition, '24h'));

        Notification::assertSentTo(
            $user,
            CompetitionReminderNotification::class,
            function (CompetitionReminderNotification $notification) {
                return $notification->competition->name === 'Test Comp' && $notification->type === '24h';
            }
        );
    }

    public function test_competition_results_notification(): void
    {
        Notification::fake();

        $competition = Competition::factory()->ranked()->create(['name' => 'Ranked Comp']);
        $user = User::factory()->create();

        $user->notify(new CompetitionResultsNotification($competition));

        Notification::assertSentTo(
            $user,
            CompetitionResultsNotification::class,
            function (CompetitionResultsNotification $notification) {
                return $notification->competition->name === 'Ranked Comp'
                    && $notification->competition->club_ranking !== null;
            }
        );
    }

    // ─── API Tests ──────────────────────────────────────────────────────

    public function test_api_index_returns_competitions(): void
    {
        Competition::factory()->count(3)->create();

        $response = $this->getJson('/api/competitions');

        $response->assertSuccessful()
            ->assertJsonStructure(['data']);
    }

    public function test_api_index_supports_pagination(): void
    {
        Competition::factory()->count(5)->create();

        $response = $this->getJson('/api/competitions?per_page=2');

        $response->assertSuccessful();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_api_index_filters_by_type(): void
    {
        Competition::factory()->ctf()->create(['name' => 'CTF Only']);
        Competition::factory()->hackathon()->create(['name' => 'Hackathon Only']);

        $response = $this->getJson('/api/competitions?type=ctf');

        $response->assertSuccessful();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('CTF Only', $response->json('data.0.name'));
    }

    public function test_api_show_returns_single_competition(): void
    {
        $competition = Competition::factory()->create(['name' => 'Single Comp']);

        $response = $this->getJson("/api/competitions/{$competition->id}");

        $response->assertSuccessful()
            ->assertJsonPath('data.name', 'Single Comp');
    }

    public function test_api_join_requires_authentication(): void
    {
        $competition = Competition::factory()->create();

        $response = $this->postJson("/api/competitions/{$competition->id}/join");

        $response->assertUnauthorized();
    }

    public function test_api_leave_requires_authentication(): void
    {
        $competition = Competition::factory()->create();

        $response = $this->postJson("/api/competitions/{$competition->id}/leave");

        $response->assertUnauthorized();
    }

    public function test_api_authenticated_user_can_join_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/competitions/{$competition->id}/join", [
                'role' => 'member',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Successfully joined the competition.');

        $this->assertDatabaseHas('competition_participants', [
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function test_api_cannot_join_twice(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/competitions/{$competition->id}/join");

        $response->assertStatus(409);
    }

    public function test_api_authenticated_user_can_leave_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        CompetitionParticipants::factory()->create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/competitions/{$competition->id}/leave");

        $response->assertSuccessful()
            ->assertJsonPath('message', 'Successfully left the competition.');

        $this->assertDatabaseMissing('competition_participants', [
            'competition_id' => $competition->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_api_cannot_leave_unjoined_competition(): void
    {
        $user = User::factory()->create()->assignRole('member');
        $competition = Competition::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/competitions/{$competition->id}/leave");

        $response->assertNotFound();
    }
}
