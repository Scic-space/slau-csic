<?php

use App\Livewire\SupportPage;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the support page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('support'))
        ->assertOk()
        ->assertSeeLivewire(SupportPage::class);
});

it('pre-fills the senders name and email', function () {
    $user = User::factory()->create(['name' => 'Grace Namutebi', 'email' => 'grace@example.com']);

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->assertSet('name', 'Grace Namutebi')
        ->assertSet('email', 'grace@example.com');
});

it('stores a contact message when submitted', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('topic', 'Membership')
        ->set('message', 'How do I renew my membership?')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $this->assertDatabaseHas('contact_messages', [
        'name' => $user->name,
        'email' => $user->email,
        'topic' => 'Membership',
        'message' => 'How do I renew my membership?',
    ]);
});

it('requires a topic and message', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(SupportPage::class)
        ->call('submit')
        ->assertHasErrors([
            'topic' => 'required',
            'message' => 'required',
        ]);

    expect(ContactMessage::count())->toBe(0);
});
