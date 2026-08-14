<?php

use App\Livewire\MemberDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the complete your profile prompt when the profile is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('Complete Your Profile');
});

it('counts profile fields stored on member_profiles and social_links', function () {
    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/avatar.jpg',
    ]);

    $user->memberProfile()->create([
        'phone' => '0700000001',
        'bio' => 'A keen security enthusiast.',
        'headline' => 'Security Researcher',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
    ]);

    $user->socialLinks()->create([
        'github_username' => 'kevin',
        'linkedin_url' => 'https://linkedin.com/in/kevin',
        'discord_username' => 'kevin',
    ]);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertDontSee('Complete Your Profile');
});

it('does not count profile fields that live on the users table columns only', function () {
    $user = User::factory()->create([
        'registration_number' => 'BACS/26D/U/A0000',
        'profile_photo' => 'profile-photos/avatar.jpg',
    ]);

    $user->update([
        'phone' => '0700000001',
        'program' => 'Bachelor of Science in Computer Science (BSCS)',
        'bio' => 'A keen security enthusiast.',
    ]);

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('Your profile is 31% complete.');
});
