<?php

use App\Livewire\PollListing;
use App\Livewire\PollShow;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
});

it('renders the poll listing page', function () {
    Livewire::actingAs($this->user)
        ->test(PollListing::class)
        ->assertStatus(200)
        ->assertSee('Polls');
});

it('shows empty state when no polls exist', function () {
    Livewire::actingAs($this->user)
        ->test(PollListing::class)
        ->assertSee('No polls available');
});

it('shows published polls on listing', function () {
    $poll = Poll::factory()->published()->create([
        'question' => 'What is the best hacking tool?',
        'created_by' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(PollListing::class)
        ->assertSee('What is the best hacking tool?');
});

it('hides unpublished polls from listing', function () {
    Poll::factory()->create([
        'question' => 'Secret internal poll',
        'created_by' => $this->user->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(PollListing::class)
        ->assertDontSee('Secret internal poll');
});

it('renders the poll detail page', function () {
    $poll = Poll::factory()->published()->create([
        'question' => 'Favorite cybersecurity tool?',
        'created_by' => $this->user->id,
    ]);

    PollOption::create(['poll_id' => $poll->id, 'label' => 'Nmap', 'sort_order' => 0]);
    PollOption::create(['poll_id' => $poll->id, 'label' => 'Burp Suite', 'sort_order' => 1]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->assertSee('Favorite cybersecurity tool?')
        ->assertSee('Nmap')
        ->assertSee('Burp Suite');
});

it('allows voting on a poll', function () {
    $poll = Poll::factory()->active()->create([
        'question' => 'Best OS for hacking?',
        'created_by' => $this->user->id,
    ]);

    $option = PollOption::create(['poll_id' => $poll->id, 'label' => 'Kali Linux', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->set('selectedOptions', [$option->id])
        ->call('vote');

    $this->assertDatabaseHas('poll_votes', [
        'poll_id' => $poll->id,
        'option_id' => $option->id,
        'user_id' => $this->user->id,
    ]);

    $poll->refresh();
    $this->assertEquals(1, $poll->votes_count);
    $option->refresh();
    $this->assertEquals(1, $option->votes_count);
});

it('prevents duplicate voting', function () {
    $poll = Poll::factory()->active()->create([
        'question' => 'Best OS for hacking?',
        'created_by' => $this->user->id,
    ]);

    $option = PollOption::create(['poll_id' => $poll->id, 'label' => 'Kali Linux', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->set('selectedOptions', [$option->id])
        ->call('vote');

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->set('selectedOptions', [$option->id])
        ->call('vote');

    $this->assertEquals(1, PollVote::where('poll_id', $poll->id)->where('user_id', $this->user->id)->count());
});

it('shows results for expired polls', function () {
    $poll = Poll::factory()->expired()->create([
        'question' => 'Past poll question?',
        'created_by' => $this->user->id,
    ]);

    PollOption::create(['poll_id' => $poll->id, 'label' => 'Option A', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->assertSee('This poll has expired')
        ->assertDontSee('Cast Vote');
});

it('auto-generates slug from question', function () {
    $poll = Poll::create([
        'question' => 'What is the best cybersecurity framework?',
        'created_by' => $this->user->id,
    ]);

    $this->assertEquals('what-is-the-best-cybersecurity-framework', $poll->slug);
});

it('returns 404 for non-existent poll', function () {
    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => 'non-existent-slug'])
        ->assertStatus(404);
});

it('returns 404 for unpublished poll detail', function () {
    $poll = Poll::factory()->create([
        'question' => 'Unpublished secret poll',
        'is_published' => false,
        'created_by' => $this->user->id,
    ]);

    PollOption::create(['poll_id' => $poll->id, 'label' => 'Option', 'sort_order' => 0]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->assertStatus(404);
});

it('supports multiple choice voting', function () {
    $poll = Poll::factory()->active()->create([
        'question' => 'Which languages do you know?',
        'allow_multiple' => true,
        'created_by' => $this->user->id,
    ]);

    $opt1 = PollOption::create(['poll_id' => $poll->id, 'label' => 'Python', 'sort_order' => 0]);
    $opt2 = PollOption::create(['poll_id' => $poll->id, 'label' => 'Bash', 'sort_order' => 1]);

    Livewire::actingAs($this->user)
        ->test(PollShow::class, ['slug' => $poll->slug])
        ->set('selectedOptions', [$opt1->id, $opt2->id])
        ->call('vote');

    $this->assertEquals(2, PollVote::where('poll_id', $poll->id)->where('user_id', $this->user->id)->count());

    $poll->refresh();
    $this->assertEquals(2, $poll->votes_count);
});
