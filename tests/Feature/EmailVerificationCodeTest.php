<?php

use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function emailVerificationRegistrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Kevin Ssali',
        'email' => 'kevin@example.com',
        'registration_number' => 'BACS/26D/U/A0000',
        'phone' => '0700000001',
        'program' => 'Bachelor of Information Technology (BIT)',
        'faculty' => 'Faculty of Science & Technology',
        'year_of_study' => 3,
        'intake' => 'august',
        'intake_year' => 2024,
        'password' => 'Passw0rd!',
        'password_confirmation' => 'Passw0rd!',
        'terms' => '1',
    ], $overrides);
}

it('sends a verification code on registration and redirects to the notice page', function () {
    Notification::fake();

    $response = $this->post('/auth/register', emailVerificationRegistrationData());

    $response->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'kevin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->email_verification_code)->not->toBeNull()
        ->and($user->email_verification_code_expires_at)->not->toBeNull();

    Notification::assertSentTo(
        $user,
        EmailVerificationCodeNotification::class,
        fn (EmailVerificationCodeNotification $notification): bool => Hash::check(
            $notification->code,
            $user->email_verification_code,
        ),
    );

    $this->assertAuthenticatedAs($user);
});

it('verifies the email address with a valid code', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();
    $code = $user->generateEmailVerificationCode();

    $this->actingAs($user)
        ->post('/auth/verify-email/verify', ['code' => $code])
        ->assertRedirect(route('dashboard', ['verified' => 1]));

    $user->refresh();

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->email_verification_code)->toBeNull()
        ->and($user->email_verification_code_expires_at)->toBeNull();

    Event::assertDispatched(Verified::class);
});

it('returns an inertia location to the dashboard after a valid code', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();
    $code = $user->generateEmailVerificationCode();

    $this->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post('/auth/verify-email/verify', ['code' => $code])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('dashboard', ['verified' => 1]));

    $user->refresh();

    expect($user->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class);
});

it('rejects an incorrect verification code', function () {
    $user = User::factory()->unverified()->create();
    $user->generateEmailVerificationCode();

    $this->actingAs($user)
        ->from('/auth/verify-email')
        ->post('/auth/verify-email/verify', ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects an expired verification code', function () {
    $user = User::factory()->unverified()->create();
    $code = $user->generateEmailVerificationCode();
    $user->update(['email_verification_code_expires_at' => now()->subMinute()]);

    $this->actingAs($user)
        ->from('/auth/verify-email')
        ->post('/auth/verify-email/verify', ['code' => $code])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects a malformed verification code', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->from('/auth/verify-email')
        ->post('/auth/verify-email/verify', ['code' => '12ab'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('resends a new verification code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('/auth/verify-email/resend')
        ->assertRedirect()
        ->assertSessionHas('status', 'verification-code-sent');

    $user->refresh();

    expect($user->email_verification_code)->not->toBeNull();

    Notification::assertSentTo($user, EmailVerificationCodeNotification::class);
});

it('redirects verified users to the dashboard when verifying', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/auth/verify-email/verify', ['code' => '123456'])
        ->assertRedirect(route('dashboard'));
});

it('redirects verified users to the dashboard when resending', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/auth/verify-email/resend')
        ->assertRedirect(route('dashboard'));
});

it('returns an inertia location to the dashboard for verified users when verifying', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post('/auth/verify-email/verify', ['code' => '123456'])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('dashboard'));
});

it('returns an inertia location to the dashboard for verified users when resending', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post('/auth/verify-email/resend')
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', route('dashboard'));
});
