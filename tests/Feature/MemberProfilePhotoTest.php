<?php

use App\Livewire\MemberProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('stores an uploaded profile photo', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->set('profile_photo', UploadedFile::fake()->image('avatar.jpg'))
        ->assertHasNoErrors()
        ->assertDispatched('profile-photo-updated')
        ->assertDispatched('toast-show')
        ->assertSet('profile_photo', null);

    $user->refresh();

    expect($user->profile_photo)->not->toBeNull();
    expect($component->get('profilePhotoUrl'))
        ->toContain('/storage/'.$user->profile_photo)
        ->toContain('?v=');
    Storage::disk('public')->assertExists($user->profile_photo);
});

it('replaces an existing profile photo when a new one is uploaded', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'profile_photo' => 'profile-photos/old-avatar.jpg',
    ]);

    Storage::disk('public')->put('profile-photos/old-avatar.jpg', 'old');

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->set('profile_photo', UploadedFile::fake()->image('new-avatar.jpg'))
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->profile_photo)->not->toBe('profile-photos/old-avatar.jpg');
    Storage::disk('public')->assertMissing('profile-photos/old-avatar.jpg');
    Storage::disk('public')->assertExists($user->profile_photo);
});

it('keeps the uploaded profile photo after a fresh authenticated session', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->set('profile_photo', UploadedFile::fake()->image('persistent-avatar.jpg'));

    $storedPath = $user->refresh()->profile_photo;

    Livewire::actingAs(User::query()->findOrFail($user->id))
        ->test(MemberProfile::class)
        ->assertSet('profilePhotoUrl', Storage::disk('public')->url($storedPath))
        ->assertSee(Storage::disk('public')->url($storedPath));
});
