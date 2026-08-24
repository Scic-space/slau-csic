<?php

use App\Livewire\MemberProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders compact profile cards with icons and save labels', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->assertSee('Personal Information')
        ->assertSee('material-symbols-outlined')
        ->assertSee('rounded-sm')
        ->assertSee('profile-form')
        ->assertSee('profile-input-group')
        ->assertSee('Save')
        ->assertDontSee('Save Changes');
});

it('uses the shared dashboard card and TailAdmin-style profile form primitives', function () {
    $profile = file_get_contents(resource_path('views/livewire/member-profile.blade.php'));
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($profile)
        ->toContain('py-4 sm:py-5')
        ->toContain('dashboard-card rounded-sm')
        ->toContain('profile-form')
        ->toContain('profile-input-group');

    expect($styles)
        ->toContain('.dashboard-stat .material-symbols-outlined')
        ->toContain('.profile-form :is(input:not(')
        ->toContain('min-height: 2.75rem');
});

it('shows a confirmation toast after saving profile information', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->set('name', 'Updated Member')
        ->set('email', 'updated@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors()
        ->assertDispatched('toast-show', message: 'Profile updated successfully.', type: 'success');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Member',
        'email' => 'updated@example.com',
    ]);
});

it('requires explicit confirmation and a valid password before deletion', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->call('confirmDelete')
        ->assertSet('confirmingDelete', true)
        ->assertSee('This action is permanent and cannot be undone.')
        ->set('delete_password', 'incorrect-password')
        ->call('deleteAccount')
        ->assertHasErrors(['delete_password']);

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
