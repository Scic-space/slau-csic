<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows unverified admins to access the panel', function (string $role) {
    $admin = User::factory()->unverified()->create()->assignRole($role);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();

    expect($admin->fresh()->email_verified_at)->toBeNull();
})->with(['admin', 'super-admin', 'Treasurer', 'President']);

it('allows verified admins to access the panel', function () {
    $admin = User::factory()->create()->assignRole('super-admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('blocks suspended admins from the panel', function () {
    $admin = User::factory()->unverified()->create([
        'membership_status' => 'suspended',
        'suspension_reason' => 'Testing denial of access',
    ])->assignRole('super-admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertForbidden();
});

it('blocks members without a panel role from accessing the panel', function () {
    $member = User::factory()->unverified()->create();

    $this->actingAs($member)
        ->get('/admin')
        ->assertForbidden();
});

it('requires guests to log in before accessing the panel', function () {
    $this->get('/admin')
        ->assertRedirect(route('auth.login'));
});

it('does not require email verification on authenticated panel routes', function () {
    expect(Filament::getPanel('admin')->getAuthMiddleware())
        ->not->toContain(EnsureEmailIsVerified::class);
});

it('applies the security headers middleware to the admin panel', function () {
    expect(Filament::getPanel('admin')->getMiddleware())
        ->toContain(AddSecurityHeaders::class);
});

it('registers the custom login page with per-account rate limiting', function () {
    $loginRouteAction = Filament::getPanel('admin')->getLoginRouteAction();

    expect($loginRouteAction)->toBe(\App\Filament\Pages\Auth\Login::class)
        ->and(is_subclass_of($loginRouteAction, \Filament\Auth\Pages\Login::class))->toBeTrue();
});
