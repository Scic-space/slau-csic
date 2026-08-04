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

    Livewire::actingAs($user)
        ->test(MemberProfile::class)
        ->set('profile_photo', UploadedFile::fake()->image('avatar.jpg'))
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->profile_photo)->not->toBeNull();
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
