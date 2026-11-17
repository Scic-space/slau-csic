<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Livewire\MemberProfile;
use App\Models\User;
use App\Notifications\MemberPasswordResetNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create([
        'membership_status' => 'active',
        'membership_type' => 'active',
    ]);
    $this->admin->assignRole('super-admin');
});

it('generates a temporary password that replaces the old one', function () {
    $member = User::factory()->create();

    $temporary = $member->resetPasswordByAdmin($this->admin);
    $member->refresh();

    expect($member->force_password_change)->toBeTrue()
        ->and($member->password_reset_by)->toBe($this->admin->id)
        ->and($member->password_reset_at)->not->toBeNull()
        ->and(Hash::check($temporary, $member->password))->toBeTrue()
        ->and(Hash::check('password', $member->password))->toBeFalse();
});

it('emails the temporary password to the member', function () {
    Notification::fake();

    $member = User::factory()->create();
    $temporary = $member->resetPasswordByAdmin($this->admin);
    $member->notify(new MemberPasswordResetNotification($temporary));

    Notification::assertSentTo($member, MemberPasswordResetNotification::class, function ($notification) use ($temporary) {
        return $notification->temporaryPassword === $temporary;
    });
});

it('redirects a flagged member to their profile from other pages', function () {
    $member = User::factory()->create(['force_password_change' => true]);

    $this->actingAs($member)
        ->get('/dashboard')
        ->assertRedirect(route('profile.edit'));
});

it('lets a flagged member reach the profile page', function () {
    $member = User::factory()->create(['force_password_change' => true]);

    $this->actingAs($member)
        ->get(route('profile.edit'))
        ->assertSuccessful();
});

it('clears the flag once the member sets a new password', function () {
    $member = User::factory()->create(['force_password_change' => true]);

    Livewire::actingAs($member)
        ->test(MemberProfile::class)
        ->set('current_password', 'password')
        ->set('new_password', 'fresh-secret-password')
        ->set('new_password_confirmation', 'fresh-secret-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect($member->refresh()->force_password_change)->toBeFalse();

    $this->actingAs($member->refresh())
        ->get('/dashboard')
        ->assertSuccessful();
});

it('lets a super-admin reset a password from the users table', function () {
    Notification::fake();

    $member = User::factory()->create();

    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListUsers::class)
        ->callTableAction('reset_password', $member);

    $member->refresh();

    expect($member->force_password_change)->toBeTrue();

    Notification::assertSentTo($member, MemberPasswordResetNotification::class);
});
