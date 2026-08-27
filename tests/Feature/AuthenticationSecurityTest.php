<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('login:ip:'.hash('sha256', '127.0.0.1'));
});

it('authenticates valid credentials with the server managed session', function () {
    $user = User::factory()->create([
        'email' => 'member@example.com',
        'password' => Hash::make('SecurePass1!'),
    ]);

    $this->post('/auth/login', [
        'email' => 'member@example.com',
        'password' => 'SecurePass1!',
        'remember' => true,
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

it('returns the same generic error for an unknown account and an incorrect password', function () {
    User::factory()->create([
        'email' => 'member@example.com',
        'password' => Hash::make('SecurePass1!'),
    ]);

    $unknown = $this->from('/auth/login')->post('/auth/login', [
        'email' => 'unknown@example.com',
        'password' => 'WrongPass1!',
    ]);

    $incorrect = $this->from('/auth/login')->post('/auth/login', [
        'email' => 'member@example.com',
        'password' => 'WrongPass1!',
    ]);

    $unknown->assertSessionHasErrors(['email' => __('auth.failed')]);
    $incorrect->assertSessionHasErrors(['email' => __('auth.failed')]);
});

it('does not authenticate or expose a database error for injection-shaped credentials', function () {
    $this->from('/auth/login')->post('/auth/login', [
        'email' => "' OR 1=1 --@example.com",
        'password' => "' OR 1=1 --",
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rate limits repeated attempts against an account across different IP addresses', function () {
    $email = 'target@example.com';
    $accountKey = 'login:account:'.hash('sha256', $email);
    RateLimiter::clear($accountKey);

    foreach (range(1, 5) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "192.0.2.{$attempt}"])
            ->from('/auth/login')
            ->post('/auth/login', [
                'email' => $email,
                'password' => 'WrongPass1!',
            ])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.100'])
        ->from('/auth/login')
        ->post('/auth/login', [
            'email' => $email,
            'password' => 'WrongPass1!',
        ])
        ->assertSessionHasErrors('email');

    expect(RateLimiter::tooManyAttempts($accountKey, 5))->toBeTrue();
});

it('uses the same password reset response whether an account exists or not', function () {
    User::factory()->create(['email' => 'member@example.com']);

    $known = $this->post('/auth/forgot-password', ['email' => 'member@example.com']);
    $unknown = $this->post('/auth/forgot-password', ['email' => 'unknown@example.com']);
    $message = 'If an account exists for that email, a password reset link has been sent.';

    $known->assertSessionHas('status', $message);
    $unknown->assertSessionHas('status', $message);
});

it('adds safe baseline security headers to web responses', function () {
    $this->get('/auth/login')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('rate limits repeated registration submissions at the route boundary', function () {
    $payload = [
        'name' => '',
        'email' => 'invalid',
        'registration_number' => '',
        'phone' => '',
        'program' => '',
        'faculty' => '',
        'year_of_study' => '',
        'intake' => '',
        'intake_year' => '',
        'password' => '',
        'password_confirmation' => '',
        'terms' => false,
    ];

    foreach (range(1, 5) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])
            ->post('/auth/register', $payload)
            ->assertSessionHasErrors();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])
        ->post('/auth/register', $payload)
        ->assertTooManyRequests();
});

it('shows compact authentication help without exposing the WhatsApp number', function () {
    $source = file_get_contents(resource_path('js/pages/auth/Login.tsx'));

    expect($source)
        ->toContain('mailto:sciccyber8@gmail.com')
        ->toContain('https://wa.me/254105883177')
        ->toContain('Email SCIC Cyber for help')
        ->toContain('WhatsApp Help')
        ->not->toContain('+254105883177')
        ->not->toContain('+254 105 883 177')
        ->not->toContain('Secure access to the SCIC Cyber platform');
});

it('keeps the login interface lightweight and accessible', function () {
    $form = file_get_contents(resource_path('js/components/ui/login-form.tsx'));
    $authInput = file_get_contents(resource_path('js/components/ui/auth-input.tsx'));
    $page = file_get_contents(resource_path('js/pages/auth/Login.tsx'));

    expect($form)
        ->toContain('authControlClass')
        ->toContain('<AuthInputIcon>mail</AuthInputIcon>')
        ->toContain('<AuthInputIcon>lock</AuthInputIcon>')
        ->toContain('aria-invalid')
        ->toContain('aria-busy={processing}')
        ->not->toContain('bg-white')
        ->not->toContain('bg-background pl-11')
        ->not->toContain('shadow-theme-sm backdrop-blur-sm');

    expect($authInput)
        ->toContain('auth-input h-11 w-full rounded-sm border bg-input pl-10 pr-3 text-sm')
        ->toContain('focus:border-brand-500 focus:ring-3 focus:ring-brand-500/10')
        ->not->toContain('bg-white');

    $styles = file_get_contents(resource_path('css/app.css'));

    expect($styles)
        ->toContain('.auth-input:-webkit-autofill')
        ->toContain('-webkit-text-fill-color: var(--foreground)')
        ->toContain('var(--input) inset');

    expect($page)
        ->toContain('min-h-screen')
        ->toContain('flex-1')
        ->toContain('flex-wrap')
        ->toContain('ThemeToggle');
});
