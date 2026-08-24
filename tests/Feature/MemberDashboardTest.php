<?php

use App\Livewire\MemberDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('shows the authenticated user a time-appropriate dashboard greeting', function (string $time, string $greeting) {
    Carbon::setTestNow($time);

    $user = User::factory()->create([
        'name' => 'Amina Nsubuga',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee("{$greeting}, Amina Nsubuga");
})->with([
    'morning before five' => ['2026-08-22 04:59:00', 'Good morning'],
    'morning from five' => ['2026-08-22 05:00:00', 'Good morning'],
    'afternoon from noon' => ['2026-08-22 12:00:00', 'Good afternoon'],
    'evening from five' => ['2026-08-22 17:00:00', 'Good evening'],
]);

it('shows the complete your profile prompt when the profile is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSee('Complete Your Profile');
});

it('renders meaningful Material Symbols for dashboard cards', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MemberDashboard::class)
        ->assertSeeHtml('material-symbols-outlined')
        ->assertSeeInOrder([
            'event',
            'event_available',
            'local_fire_department',
            'stars',
            'fact_check',
            'emoji_events',
            'calendar_month',
            'school',
            'military_tech',
            'history',
        ]);
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
