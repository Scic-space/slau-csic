<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

afterEach(function () {
    Carbon::setTestNow();
});

it('shows the shared footer throughout the authenticated user area', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('All rights reserved &copy; SCIC Cyber', escape: false)
        ->assertSee('sciccyber8@gmail.com')
        ->assertSee('system-footer-fixed', escape: false)
        ->assertSee('pb-20', escape: false)
        ->assertSeeHtml('material-symbols-outlined');
});

it('shows the admin greeting, Material Icons stylesheet, and shared footer', function () {
    Carbon::setTestNow('2026-08-23 14:00:00');

    $admin = User::factory()->create([
        'name' => 'Grace Admin',
    ]);
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('Good afternoon, Grace Admin')
        ->assertSee('Material Symbols Outlined')
        ->assertSee('All rights reserved &copy; SCIC Cyber', escape: false)
        ->assertSee('sciccyber8@gmail.com')
        ->assertDontSee('system-footer-fixed', escape: false);
});
