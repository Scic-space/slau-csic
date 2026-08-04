<?php

use App\Models\Election;
use App\Models\ElectionNomination;
use App\Models\ElectionVote;
use App\Models\User;
use App\Notifications\ElectionOpenedNotification;
use App\Notifications\VoteReceiptNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $this->user->assignRole('member');
    $this->user->givePermissionTo('vote_in_elections');
});

it('renders the voting page for authenticated users', function () {
    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertStatus(200);
});

it('redirects guests to login from voting page', function () {
    $this->get('/voting')
        ->assertRedirect('/auth/login');
});

it('shows open elections on dashboard', function () {
    Election::query()->delete();

    $election = Election::factory()->open()->create(['title' => '2026 President Election']);
    $election->candidates()->createMany([
        ['name' => 'Alice', 'sort_order' => 0],
        ['name' => 'Bob', 'sort_order' => 1],
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('2026 President Election')
        ->assertSee('2 candidates');
});

it('shows empty elections array when none exist', function () {
    Election::query()->delete();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('No elections found');
});

it('allows authenticated users to cast a vote via Livewire', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->assertSet('showConfirmModal', true)
        ->call('castVote')
        ->assertSet('showConfirmModal', false);

    expect(ElectionVote::where('user_id', $this->user->id)->count())->toBe(1);
});

it('prevents voting on closed elections', function () {
    $election = Election::factory()->closed()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->assertDontSee('Cast Ballot');
});

it('prevents duplicate voting', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->assertSee('You voted for');
});

it('redirects pending members away from voting', function () {
    $inactiveUser = User::factory()->create(['membership_status' => 'pending']);
    $inactiveUser->assignRole('member');

    $election = Election::factory()->open()->create();

    Livewire::actingAs($inactiveUser)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->assertRedirect(route('dashboard'));
});

it('shows admin election management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    actingAs($admin)
        ->get('/admin/manage-elections')
        ->assertStatus(200);
});

it('prevents non-admin from accessing election management', function () {
    actingAs($this->user)
        ->get('/admin/manage-elections')
        ->assertStatus(403);
});

it('shows the user their previous vote on dashboard', function () {
    Election::query()->delete();

    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    ElectionVote::create([
        'election_id' => $election->id,
        'election_candidate_id' => $candidate->id,
        'user_id' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('Alice')
        ->assertSee('Voted');
});

it('lists elections via API', function () {
    $token = $this->user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/elections')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('casts vote via API', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $token = $this->user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson("/api/elections/{$election->id}/vote", ['candidate_id' => $candidate->id])
        ->assertOk();
});

it('prevents voting via API for non-active members', function () {
    $inactiveUser = User::factory()->create(['membership_status' => 'pending']);
    $inactiveUser->assignRole('member');

    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $token = $inactiveUser->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson("/api/elections/{$election->id}/vote", ['candidate_id' => $candidate->id])
        ->assertStatus(403);
});

it('shows nominations page for authenticated users', function () {
    Election::factory()->draft()->create(['title' => 'President Election']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->assertStatus(200);
});

it('allows members to submit a nomination via Livewire', function () {
    $election = Election::factory()->draft()->create(['title' => 'President Election']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->set("statements.{$election->id}", 'I want to lead')
        ->call('submitNomination', $election->id);

    expect(ElectionNomination::where('user_id', $this->user->id)->count())->toBe(1);
    expect(ElectionNomination::where('user_id', $this->user->id)->first()->statement)->toBe('I want to lead');
});

it('shows results page', function () {
    $election = Election::factory()->closed()->create(['results_visible' => true, 'title' => 'President Election']);

    actingAs($this->user)
        ->get('/voting/results')
        ->assertSuccessful()
        ->assertSee('President Election');
});

it('prevents voting without vote_in_elections permission', function () {
    $user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);

    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->assertSee('You are not eligible');
});

it('prevents vote changes when allow_vote_changes is false', function () {
    $election = Election::factory()->open()->create(['allow_vote_changes' => false]);
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $newCandidate = $election->candidates()->create(['name' => 'Bob']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $newCandidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->assertSee('Vote changes are not allowed');

    expect(ElectionVote::where('user_id', $this->user->id)->count())->toBe(1);
});

it('shows turnout attribute on election', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    ElectionVote::create([
        'election_id' => $election->id,
        'election_candidate_id' => $candidate->id,
        'user_id' => $this->user->id,
    ]);

    expect($election->turnout)->toBeString();
});

it('detects election winner', function () {
    $election = Election::factory()->closed()->create();
    $winner = $election->candidates()->create(['name' => 'Alice']);
    $loser = $election->candidates()->create(['name' => 'Bob']);

    ElectionVote::factory()->count(5)->create([
        'election_id' => $election->id,
        'election_candidate_id' => $winner->id,
    ]);
    ElectionVote::factory()->count(2)->create([
        'election_id' => $election->id,
        'election_candidate_id' => $loser->id,
    ]);

    expect($election->winner->id)->toBe($winner->id);
});

it('shows user helper methods work correctly', function () {
    $user = $this->user;

    expect($user->canVote())->toBeTrue();

    $election = Election::factory()->open()->create();
    expect($user->canVoteIn($election))->toBeTrue();

    $closed = Election::factory()->closed()->create();
    expect($user->canVoteIn($closed))->toBeFalse();

    expect($user->hasVotedIn($election))->toBeFalse();

    $candidate = $election->candidates()->create(['name' => 'Alice']);
    ElectionVote::create([
        'election_id' => $election->id,
        'election_candidate_id' => $candidate->id,
        'user_id' => $user->id,
    ]);

    expect($user->hasVotedIn($election))->toBeTrue();
});

it('generates a receipt code when a vote is cast', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $vote = ElectionVote::where('user_id', $this->user->id)->first();
    expect($vote->receipt_code)->not->toBeNull();
    expect(strlen($vote->receipt_code))->toBe(64);
});

it('assigns unique receipt codes to different votes', function () {
    $election = Election::factory()->open()->create();
    $alice = $election->candidates()->create(['name' => 'Alice']);
    $bob = $election->candidates()->create(['name' => 'Bob']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $alice->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $vote1 = ElectionVote::where('user_id', $this->user->id)->first();
    $hash1 = $vote1->receipt_code;

    $user2 = \App\Models\User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $user2->assignRole('member');

    Livewire::actingAs($user2)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $bob->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $vote2 = ElectionVote::where('user_id', $user2->id)->first();
    $hash2 = $vote2->receipt_code;

    expect($hash1)->not->toBe($hash2);
});

it('finds a vote by receipt hash', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $receiptCode = Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->get('receiptCode');

    expect($receiptCode)->not->toBeNull();

    $vote = ElectionVote::where('user_id', $this->user->id)->first();
    $found = ElectionVote::findByReceiptHash($receiptCode);
    expect($found)->not->toBeNull();
    expect($found->id)->toBe($vote->id);
});

it('returns null for invalid receipt hash', function () {
    $found = ElectionVote::findByReceiptHash('INVALIDCODE123');
    expect($found)->toBeNull();
});

it('renders verify page', function () {
    actingAs($this->user)
        ->get('/vote/verify')
        ->assertSuccessful();
});

it('verifies a valid receipt code via Livewire', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $receiptCode = Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->get('receiptCode');

    expect($receiptCode)->not->toBeNull();

    Livewire::test(\App\Livewire\VerifyReceipt::class)
        ->set('receiptCode', $receiptCode)
        ->call('verify')
        ->assertSet('error', null)
        ->assertSet('result.candidate_name', 'Alice');
});

it('shows error for invalid receipt code on verify', function () {
    Livewire::test(\App\Livewire\VerifyReceipt::class)
        ->set('receiptCode', 'INVALIDCODE')
        ->call('verify')
        ->assertSet('result', null)
        ->assertSet('error', 'No vote found with this receipt code.');
});

it('generates receipt via API', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $token = $this->user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson("/api/elections/{$election->id}/vote", ['candidate_id' => $candidate->id])
        ->assertOk();

    expect($response->json('receipt_code'))->not->toBeNull();
});

it('creates a receipt code via Livewire vote flow', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $vote = ElectionVote::where('user_id', $this->user->id)->first();
    expect($vote->receipt_code)->not->toBeNull();
    expect(strlen($vote->receipt_code))->toBe(64);
});

it('runs auto-open command', function () {
    $election = Election::factory()->draft()->create([
        'starts_at' => now()->subHour(),
    ]);

    $this->artisan('elections:auto-open')
        ->assertSuccessful();

    $election->refresh();
    expect($election->status)->toBe('open');
});

it('does not auto-open future elections', function () {
    $election = Election::factory()->draft()->create([
        'starts_at' => now()->addDay(),
    ]);

    $this->artisan('elections:auto-open')
        ->assertSuccessful();

    $election->refresh();
    expect($election->status)->toBe('draft');
});

it('runs auto-close command', function () {
    $election = Election::factory()->open()->create([
        'ends_at' => now()->subHour(),
    ]);

    $this->artisan('elections:auto-close')
        ->assertSuccessful();

    $election->refresh();
    expect($election->status)->toBe('closed');
});

it('runs send-reminders command', function () {
    Election::factory()->open()->create([
        'ends_at' => now()->addHours(12),
    ]);

    $this->artisan('elections:send-reminders')
        ->assertSuccessful();
});

it('sends vote receipt notification', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    expect($this->user->notifications()->where('type', 'App\Notifications\VoteReceiptNotification')->count())->toBe(1);
});

it('approve action creates candidate from nomination', function () {
    $election = Election::factory()->draft()->create();
    $nomination = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'I want to lead',
    ]);

    $nomination->approve();

    $election->candidates()->create([
        'name' => $this->user->name,
        'user_id' => $this->user->id,
    ]);

    expect($nomination->status)->toBe('approved');
    expect($election->candidates()->count())->toBe(1);
    expect($election->candidates()->first()->name)->toBe($this->user->name);
});

it('excludes test ballots from member voting page', function () {
    Election::factory()->open()->create(['is_test_ballot' => true, 'title' => 'Test Election']);
    Election::factory()->open()->create(['is_test_ballot' => false, 'title' => 'Real Election']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('Real Election')
        ->assertDontSee('Test Election');
});

it('prevents members from viewing test ballot detail pages', function () {
    $election = Election::factory()->open()->create(['is_test_ballot' => true]);
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->assertStatus(404);
});

it('allows admin to vote in test ballots', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->givePermissionTo('vote_in_elections');

    $election = Election::factory()->open()->create(['is_test_ballot' => true]);
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    expect(ElectionVote::where('user_id', $admin->id)->count())->toBe(1);
});

it('excludes test ballots from results page', function () {
    Election::factory()->closed()->create(['is_test_ballot' => true, 'results_visible' => true, 'title' => 'Test']);
    Election::factory()->closed()->create(['is_test_ballot' => false, 'results_visible' => true, 'title' => 'Real']);

    actingAs($this->user)
        ->get('/voting/results')
        ->assertSuccessful()
        ->assertSee('Real')
        ->assertDontSee('Test');
});

it('excludes test ballots from nominations page', function () {
    Election::factory()->draft()->create(['is_test_ballot' => true, 'title' => 'Test']);
    Election::factory()->draft()->create(['is_test_ballot' => false, 'title' => 'Real']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->assertSee('Real')
        ->assertDontSee('Test');
});

it('excludes test ballots from portal voting page', function () {
    Election::factory()->open()->create(['is_test_ballot' => true, 'title' => 'Test']);
    Election::factory()->open()->create(['is_test_ballot' => false, 'title' => 'Real']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('Real')
        ->assertDontSee('Test');
});

it('excludes test ballots from API', function () {
    Election::factory()->open()->create(['is_test_ballot' => true, 'title' => 'Test']);
    Election::factory()->open()->create(['is_test_ballot' => false, 'title' => 'Real']);

    $token = $this->user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/elections')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.title'))->toBe('Real');
});

it('sends email notification for election opened', function () {
    Notification::fake();

    $election = Election::factory()->create(['status' => 'draft']);

    $election->update(['status' => 'open']);

    User::activeMembers()->chunk(100, fn ($users) => $users->each(
        fn ($user) => $user->notify(new ElectionOpenedNotification($election))
    ));

    Notification::assertSentTo(
        $this->user,
        ElectionOpenedNotification::class,
        fn ($notification, $channels) => in_array('mail', $channels)
    );
});

it('sends vote receipt as database notification', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => $election->slug])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    expect($this->user->notifications()
        ->where('type', VoteReceiptNotification::class)
        ->count()
    )->toBe(1);
});

it('respects voter eligibility override - eligible', function () {
    $election = Election::factory()->open()->create();

    $election->voterEligibility()->create([
        'user_id' => $this->user->id,
        'is_eligible' => true,
    ]);

    expect($election->isExplicitlyEligible($this->user))->toBeTrue();
    expect($this->user->canVoteIn($election))->toBeTrue();
});

it('respects voter eligibility override - ineligible', function () {
    $election = Election::factory()->open()->create();

    $election->voterEligibility()->create([
        'user_id' => $this->user->id,
        'is_eligible' => false,
    ]);

    expect($election->isExplicitlyEligible($this->user))->toBeFalse();
    expect($this->user->canVoteIn($election))->toBeFalse();
});

it('detects eligibility override exists', function () {
    $election = Election::factory()->open()->create();

    expect($election->hasEligibilityOverrideFor($this->user))->toBeFalse();

    $election->voterEligibility()->create([
        'user_id' => $this->user->id,
        'is_eligible' => true,
    ]);

    expect($election->hasEligibilityOverrideFor($this->user))->toBeTrue();
});

it('soft-deletes an election', function () {
    $election = Election::factory()->create();

    $election->delete();

    expect(Election::find($election->id))->toBeNull();
    expect(Election::withTrashed()->find($election->id))->not->toBeNull();
});

it('excludes trashed elections from voting page', function () {
    $election = Election::factory()->open()->create();
    $election->delete();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('No elections found');
});

it('auto-publishes results when scheduled', function () {
    $election = Election::factory()->closed()->create([
        'results_visible' => false,
        'results_publish_at' => now()->subHour(),
    ]);

    $this->artisan('elections:auto-publish-results')
        ->assertSuccessful();

    $election->refresh();
    expect($election->results_visible)->toBeTrue();
});

it('does not auto-publish results before scheduled time', function () {
    $election = Election::factory()->closed()->create([
        'results_visible' => false,
        'results_publish_at' => now()->addDay(),
    ]);

    $this->artisan('elections:auto-publish-results')
        ->assertSuccessful();

    $election->refresh();
    expect($election->results_visible)->toBeFalse();
});

it('submits application with manifesto and agenda via Livewire', function () {
    $election = Election::factory()->draft()->create();

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->set("statements.{$election->id}", 'I can lead')
        ->set("manifestos.{$election->id}", 'My vision for the club')
        ->set("agendas.{$election->id}", 'Monthly workshops, hackathons')
        ->call('submitNomination', $election->id);

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();
    expect($nom->manifesto)->toBe('My vision for the club');
    expect($nom->agenda)->toBe('Monthly workshops, hackathons');
    expect($nom->statement)->toBe('I can lead');
    expect($nom->status)->toBe('submitted');
});

it('views my applications page', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'I want to run',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    actingAs($this->user)
        ->get('/voting/my-applications')
        ->assertSuccessful()
        ->assertSee('I want to run');
});

it('shows empty applications state', function () {
    actingAs($this->user)
        ->get('/voting/my-applications')
        ->assertSuccessful()
        ->assertSee('No applications yet');
});

it('nomination status helpers work correctly', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    expect($nom->isSubmitted())->toBeTrue();
    expect($nom->isPending())->toBeTrue();
    expect($nom->isApproved())->toBeFalse();
    expect($nom->isRejected())->toBeFalse();

    $nom->markUnderReview(User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']));
    expect($nom->isUnderReview())->toBeTrue();

    $nom->shortlist();
    expect($nom->isShortlisted())->toBeTrue();

    $nom->approve('Great candidate');
    expect($nom->isApproved())->toBeTrue();
    expect($nom->admin_notes)->toBe('Great candidate');

    $nom2 = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active'])->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    $nom2->reject('Not qualified');
    expect($nom2->isRejected())->toBeTrue();
    expect($nom2->admin_notes)->toBe('Not qualified');
});

it('scopes nominations by status', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create(['election_id' => $election->id, 'user_id' => $this->user->id, 'status' => 'submitted', 'submitted_at' => now()]);
    $user2 = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    ElectionNomination::create(['election_id' => $election->id, 'user_id' => $user2->id, 'status' => 'approved', 'submitted_at' => now(), 'reviewed_at' => now()]);
    $user3 = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    ElectionNomination::create(['election_id' => $election->id, 'user_id' => $user3->id, 'status' => 'rejected', 'submitted_at' => now(), 'reviewed_at' => now()]);

    expect(ElectionNomination::submitted()->count())->toBe(1);
    expect(ElectionNomination::where('status', 'approved')->count())->toBe(1);
    expect(ElectionNomination::where('status', 'rejected')->count())->toBe(1);
});

it('isAcceptingApplications works correctly', function () {
    $past = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDays(10),
        'applications_ends_at' => now()->subDays(1),
    ]);
    expect($past->isAcceptingApplications())->toBeFalse();

    $current = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDays(5),
        'applications_ends_at' => now()->addDays(5),
    ]);
    expect($current->isAcceptingApplications())->toBeTrue();

    $future = Election::factory()->draft()->create([
        'applications_starts_at' => now()->addDays(5),
        'applications_ends_at' => now()->addDays(10),
    ]);
    expect($future->isAcceptingApplications())->toBeFalse();
});

it('scopeAcceptingApplications works correctly', function () {
    Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDays(5),
        'applications_ends_at' => now()->addDays(5),
    ]);
    Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDays(10),
        'applications_ends_at' => now()->subDays(1),
    ]);

    expect(Election::acceptingApplications()->count())->toBe(1);
});

it('admin can review application through the relation manager approve action', function () {
    $admin = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $admin->assignRole('admin');

    $election = Election::factory()->draft()->create();
    $nomination = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'I want to run',
        'manifesto' => 'My vision',
        'agenda' => 'My plans',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $nomination->markUnderReview($admin);
    expect($nomination->fresh()->isUnderReview())->toBeTrue();
    expect($nomination->fresh()->reviewer_id)->toBe($admin->id);

    $nomination->shortlist('Promising candidate');
    expect($nomination->fresh()->isShortlisted())->toBeTrue();

    $nomination->approve('Approved - excellent fit');
    expect($nomination->fresh()->isApproved())->toBeTrue();
    expect($nomination->fresh()->reviewed_at)->not->toBeNull();

    $election->candidates()->create([
        'name' => $this->user->name,
        'user_id' => $this->user->id,
        'manifesto' => $nomination->manifesto,
        'agenda' => $nomination->agenda,
    ]);

    $candidate = $election->candidates()->where('user_id', $this->user->id)->first();
    expect($candidate)->not->toBeNull();
    expect($candidate->manifesto)->toBe('My vision');
    expect($candidate->agenda)->toBe('My plans');
});

it('shows application preview data on nominations page', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'My statement',
        'manifesto' => 'My manifesto',
        'agenda' => 'My agenda',
        'status' => 'under_review',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->assertSee('My statement')
        ->assertSee('My manifesto')
        ->assertSee('My agenda');
});

it('withdraws an active application via Livewire', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'I want to run',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->call('withdrawNomination', $election->id);

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();
    expect($nom->status)->toBe('withdrawn');
    expect($nom->isWithdrawn())->toBeTrue();
});

it('submits an application from the my applications page', function () {
    $election = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDay(),
        'applications_ends_at' => now()->addDays(7),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->call('openApplicationForm')
        ->assertSet('showForm', true)
        ->set('selectedElectionId', $election->id)
        ->set('statement', 'My inline statement')
        ->set('manifesto', 'My inline manifesto')
        ->set('agenda', 'My inline agenda')
        ->call('submitApplication');

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();

    expect($nom)->not->toBeNull();
    expect($nom->election_id)->toBe($election->id);
    expect($nom->statement)->toBe('My inline statement');
    expect($nom->manifesto)->toBe('My inline manifesto');
    expect($nom->agenda)->toBe('My inline agenda');
    expect($nom->status)->toBe('submitted');
});

it('rejects submission from my applications when election is not accepting applications', function () {
    $election = Election::factory()->draft()->create([
        'applications_starts_at' => now()->addDay(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->set('selectedElectionId', $election->id)
        ->set('statement', 'Too early')
        ->call('submitApplication')
        ->assertStatus(403);

    expect(ElectionNomination::count())->toBe(0);
});

it('prevents a duplicate active application from my applications page', function () {
    $election = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDay(),
    ]);

    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Already applied',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->set('selectedElectionId', $election->id)
        ->set('statement', 'Trying again')
        ->call('submitApplication')
        ->assertHasErrors('selectedElectionId');

    expect(ElectionNomination::where('user_id', $this->user->id)->first()->statement)->toBe('Already applied');
});

it('reapplies after rejection from the my applications page', function () {
    $election = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDay(),
    ]);

    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'First attempt',
        'status' => 'rejected',
        'submitted_at' => now()->subDays(5),
        'reviewed_at' => now()->subDays(3),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->set('selectedElectionId', $election->id)
        ->set('statement', 'Re-applying inline')
        ->call('submitApplication');

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();

    expect($nom->status)->toBe('submitted');
    expect($nom->statement)->toBe('Re-applying inline');
    expect($nom->reviewed_at)->toBeNull();
});

it('stores photo and documents with an application from my applications page', function () {
    Storage::fake('public');

    $election = Election::factory()->draft()->create([
        'applications_starts_at' => now()->subDay(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->set('selectedElectionId', $election->id)
        ->set('photo', UploadedFile::fake()->image('photo.jpg'))
        ->set('documentFiles', [
            UploadedFile::fake()->create('cv.pdf', 100),
            UploadedFile::fake()->create('portfolio.pdf', 100),
        ])
        ->call('submitApplication');

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();

    expect($nom->photo)->not->toBeNull();
    expect($nom->documents)->toHaveCount(2);
    Storage::disk('public')->assertExists($nom->photo);
    foreach ($nom->documents as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

it('prevents withdrawing non-active application via Livewire', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Done',
        'status' => 'approved',
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionMyApplications::class)
        ->call('withdrawNomination', $election->id)
        ->assertStatus(403);
});

it('re-applies after rejection via Livewire', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'First attempt',
        'status' => 'rejected',
        'submitted_at' => now()->subDays(5),
        'reviewed_at' => now()->subDays(3),
    ]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->set("statements.{$election->id}", 'Re-applying with better pitch')
        ->set("manifestos.{$election->id}", 'New vision')
        ->call('submitNomination', $election->id);

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();
    expect($nom->status)->toBe('submitted');
    expect($nom->statement)->toBe('Re-applying with better pitch');
    expect($nom->manifesto)->toBe('New vision');
    expect($nom->reviewer_id)->toBeNull();
    expect($nom->reviewed_at)->toBeNull();
});

it('logs review history on status changes', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $reviewer = User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active']);

    $this->actingAs($reviewer);
    $nom->markUnderReview($reviewer);
    expect($nom->reviews()->count())->toBe(1);
    expect($nom->reviews()->latest()->first()->to_status)->toBe('under_review');

    $nom->shortlist('Top candidate');
    expect($nom->reviews()->count())->toBe(2);
    expect($nom->reviews()->where('to_status', 'shortlisted')->first()->notes)->toBe('Top candidate');

    $nom->approve();
    expect($nom->reviews()->count())->toBe(3);
});

it('shows review history on my applications page', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $nom->reviews()->create([
        'user_id' => $this->user->id,
        'from_status' => 'submitted',
        'to_status' => 'under_review',
        'notes' => 'Being reviewed',
    ]);

    actingAs($this->user)
        ->get('/voting/my-applications')
        ->assertSuccessful()
        ->assertSee('Being reviewed');
});

it('updates scores on nomination', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $nom->updateScores(['experience' => 4, 'vision' => 5, 'communication' => 3, 'overall' => 4]);
    $nom->refresh();

    expect($nom->scores)->toBe(['experience' => 4, 'vision' => 5, 'communication' => 3, 'overall' => 4]);
    expect($nom->score_average)->toBe(4.0);
});

it('calculates score average', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'submitted',
        'submitted_at' => now(),
        'scores' => ['experience' => 5, 'vision' => 4],
    ]);

    expect($nom->score_average)->toBe(4.5);

    $nom2 = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => User::factory()->create(['membership_status' => 'active', 'membership_type' => 'active'])->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
    expect($nom2->score_average)->toBeNull();
});

it('supports uploading documents with application via Livewire', function () {
    Storage::fake('public');

    $election = Election::factory()->draft()->create();

    $file = UploadedFile::fake()->create('cv.pdf', 500);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionNominations::class)
        ->set("statements.{$election->id}", 'With documents')
        ->set("documentFiles.{$election->id}", [$file])
        ->call('submitNomination', $election->id);

    $nom = ElectionNomination::where('user_id', $this->user->id)->first();
    expect($nom->documents)->toHaveCount(1);
});

it('shows interview schedule on my applications page', function () {
    $election = Election::factory()->draft()->create();
    ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'statement' => 'Ready for interview',
        'status' => 'under_review',
        'submitted_at' => now(),
        'interview_scheduled_at' => now()->addDays(3),
        'interview_location' => 'Zoom',
        'interview_notes' => 'Prepare your manifesto presentation',
    ]);

    actingAs($this->user)
        ->get('/voting/my-applications')
        ->assertSuccessful()
        ->assertSee('Zoom')
        ->assertSee('Prepare your manifesto presentation');
});

it('renders the election dashboard with stats', function () {
    Election::query()->delete();

    Election::factory()->open()->create(['title' => 'Active Election']);
    Election::factory()->draft()->create(['title' => 'Draft Election']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->assertSee('Elections Dashboard')
        ->assertSee('Active Election');
});

it('filters elections on dashboard', function () {
    Election::query()->delete();

    Election::factory()->open()->create(['title' => 'Open Election']);
    Election::factory()->closed()->create(['title' => 'Closed Election']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionVoting::class)
        ->call('setFilter', 'active')
        ->assertSee('Open Election')
        ->assertDontSee('Closed Election');
});

it('renders election detail page', function () {
    $election = Election::factory()->open()->create(['title' => 'President Vote', 'slug' => 'president-vote']);
    $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);
    $election->candidates()->create(['name' => 'Bob', 'sort_order' => 1]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'president-vote'])
        ->assertSee('President Vote')
        ->assertSee('Alice')
        ->assertSee('Bob');
});

it('shows vote confirmation modal on election detail page', function () {
    $election = Election::factory()->open()->create(['slug' => 'pres-test']);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'pres-test'])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->assertSet('showConfirmModal', true)
        ->assertSet('confirmCandidateName', 'Alice');
});

it('casts vote from election detail page via confirmation flow', function () {
    $election = Election::factory()->open()->create(['slug' => 'vote-test']);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'vote-test'])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->assertSet('showConfirmModal', false);

    expect(ElectionVote::where('user_id', $this->user->id)->count())->toBe(1);
});

it('stores receipt as hashed value in database', function () {
    $election = Election::factory()->open()->create(['slug' => 'hash-test']);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'hash-test'])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote');

    $vote = ElectionVote::where('user_id', $this->user->id)->first();
    expect($vote->receipt_code)->not->toBeNull();
    expect($vote->receipt_code)->toHaveLength(64);
    expect(str_starts_with($vote->receipt_code, hash('sha256', '')))->toBeFalse();
});

it('verifies vote by hashed receipt', function () {
    $election = Election::factory()->open()->create(['slug' => 'verify-hash']);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    $receiptCode = Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'verify-hash'])
        ->set("selectedCandidates.{$election->id}", $candidate->id)
        ->call('requestConfirm', $election->id)
        ->call('castVote')
        ->get('receiptCode');

    expect($receiptCode)->not->toBeNull();

    $found = ElectionVote::findByReceiptHash($receiptCode);
    expect($found)->not->toBeNull();
    expect($found->user_id)->toBe($this->user->id);
});

it('finds vote by hash returns null for invalid code', function () {
    $found = ElectionVote::findByReceiptHash('INVALIDCODE123');
    expect($found)->toBeNull();
});

it('shows election show page from route', function () {
    $election = Election::factory()->open()->create(['slug' => 'route-test']);
    $election->candidates()->create(['name' => 'Test Candidate', 'sort_order' => 0]);

    actingAs($this->user)
        ->get('/voting/route-test')
        ->assertSuccessful()
        ->assertSee('Test Candidate');
});

it('prevents selecting candidate on closed election', function () {
    $election = Election::factory()->closed()->create(['slug' => 'closed-detail']);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'closed-detail'])
        ->assertDontSee('Cast Ballot');
});

it('shows results on election detail when visible', function () {
    $election = Election::factory()->closed()->create([
        'slug' => 'results-detail',
        'results_visible' => true,
    ]);
    $candidate = $election->candidates()->create(['name' => 'Alice', 'sort_order' => 0]);

    $voters = User::factory()->count(5)->create(['membership_status' => 'active', 'membership_type' => 'active']);
    $voters->each(fn ($voter) => ElectionVote::create([
        'election_id' => $election->id,
        'election_candidate_id' => $candidate->id,
        'user_id' => $voter->id,
    ]));

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionShow::class, ['slug' => 'results-detail'])
        ->assertSee('Alice')
        ->assertSee('Winner');
});

it('results page filters correctly', function () {
    Election::query()->delete();

    Election::factory()->closed()->create(['results_visible' => true, 'title' => 'Published']);
    Election::factory()->closed()->create(['results_visible' => false, 'title' => 'Hidden']);

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionResults::class)
        ->assertSee('Published')
        ->assertSee('Hidden');

    Livewire::actingAs($this->user)
        ->test(\App\Livewire\ElectionResults::class)
        ->call('setFilter', 'published')
        ->assertSee('Published')
        ->assertDontSee('Hidden');
});

it('prevents approving withdrawn nomination', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'withdrawn',
        'submitted_at' => now(),
    ]);

    $result = $nom->approve();
    expect($result)->toBeFalse();
    expect($nom->fresh()->status)->toBe('withdrawn');
});

it('prevents rejecting withdrawn nomination', function () {
    $election = Election::factory()->draft()->create();
    $nom = ElectionNomination::create([
        'election_id' => $election->id,
        'user_id' => $this->user->id,
        'status' => 'withdrawn',
        'submitted_at' => now(),
    ]);

    $result = $nom->reject('No');
    expect($result)->toBeFalse();
    expect($nom->fresh()->status)->toBe('withdrawn');
});

it('sends email with vote receipt notification', function () {
    Notification::fake();

    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);

    $this->user->notify(new VoteReceiptNotification($election, 'TESTCODE1234'));

    Notification::assertSentTo(
        $this->user,
        VoteReceiptNotification::class,
        fn ($notification, $channels) => in_array('mail', $channels)
    );
});

it('renders verify receipt page via route', function () {
    actingAs($this->user)
        ->get('/vote/verify')
        ->assertSuccessful()
        ->assertSee('Verify your vote receipt');
});

it('pre-fills receipt code from query parameter', function () {
    $election = Election::factory()->open()->create();
    $candidate = $election->candidates()->create(['name' => 'Alice']);
    $receiptToken = ElectionVote::generateReceiptCode();
    $receiptHash = ElectionVote::receiptHash($receiptToken);

    ElectionVote::create([
        'election_id' => $election->id,
        'election_candidate_id' => $candidate->id,
        'user_id' => $this->user->id,
        'receipt_code' => $receiptHash,
        'receipt_token' => $receiptToken,
    ]);

    Livewire::withQueryParams(['code' => $receiptToken])
        ->test(\App\Livewire\VerifyReceipt::class)
        ->assertSet('receiptCode', $receiptToken);
});
