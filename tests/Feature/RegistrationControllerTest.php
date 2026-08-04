<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

it('redirects the legacy registration page to the Inertia registration page', function () {
    $this->get(route('register'))
        ->assertRedirect(route('auth.register'))
        ->assertSessionHasNoErrors();
});

it('redirects a legacy registration post to the Inertia registration page without creating a user', function () {
    $this->post(route('register'), [
        'name' => 'Grace Namutebi',
        'email' => 'grace@example.com',
        'registration_number' => 'BACS/26D/U/A0000',
        'phone' => '0700000001',
        'program' => 'Bachelor of Information Technology (BIT)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
        'intake' => 'august',
        'intake_year' => 2024,
        'date_of_birth' => '2002-06-04',
        'gender' => 'Female',
        'residence' => 'Nsambya Hostel',
        'profile_photo' => UploadedFile::fake()->image('passport.jpg'),
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])
        ->assertRedirect(route('auth.register'))
        ->assertSessionHasNoErrors();

    expect(User::query()->where('email', 'grace@example.com')->exists())->toBeFalse();
});

it('redirects the legacy login page and post to the Inertia login page', function () {
    $this->get(route('login'))
        ->assertRedirect(route('auth.login'))
        ->assertSessionHasNoErrors();

    $this->post(route('login'), [
        'email' => 'grace@example.com',
        'password' => 'password',
    ])
        ->assertRedirect(route('auth.login'))
        ->assertSessionHasNoErrors();
});
