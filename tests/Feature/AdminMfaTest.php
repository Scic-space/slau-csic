<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create()->assignRole('admin');
});

it('enables, verifies, and disables app authentication for a user', function () {
    $provider = AppAuthentication::make();

    expect($provider->isEnabled($this->admin))->toBeFalse();

    $secret = $provider->generateSecret();
    $provider->saveSecret($this->admin, $secret);

    $admin = $this->admin->fresh();

    expect($provider->isEnabled($admin))->toBeTrue();
    expect($provider->verifyCode($provider->getCurrentCode($admin), $admin->getAppAuthenticationSecret()))->toBeTrue();
    expect($provider->verifyCode('000000', $admin->getAppAuthenticationSecret()))->toBeFalse();

    $provider->saveSecret($admin, null);

    expect($provider->isEnabled($admin->fresh()))->toBeFalse();
});

it('stores the secret encrypted at rest', function () {
    $provider = AppAuthentication::make();

    $provider->saveSecret($this->admin, 'TEST-SECRET-12345');

    expect($this->admin->fresh()->getRawOriginal('app_authentication_secret'))->not->toBe('TEST-SECRET-12345');
    expect($this->admin->fresh()->getRawOriginal('app_authentication_secret'))->not->toBeNull();
});

it('stores and verifies hashed recovery codes', function () {
    $provider = AppAuthentication::make();

    $codes = $provider->generateRecoveryCodes();
    $provider->saveRecoveryCodes($this->admin, $codes);

    $admin = $this->admin->fresh();

    expect($admin->getAppAuthenticationRecoveryCodes())->toHaveCount(8);
    expect($provider->verifyRecoveryCode($codes[0], $admin))->toBeTrue();
    expect($provider->verifyRecoveryCode('not-a-real-code', $admin))->toBeFalse();
});

it('loads the admin profile page with the two-factor section', function () {
    $this->actingAs($this->admin)
        ->get('/admin/my-profile')
        ->assertSuccessful()
        ->assertSee('Two-Factor Authentication');
});
