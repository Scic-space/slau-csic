<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('allows super-admin to access assignment wizard', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get('/admin/assignment-wizard')
        ->assertOk();
});

it('allows user with view_assignments permission to view role templates', function () {
    $user = User::factory()->create();
    $user->assignRole('President');

    $this->actingAs($user)
        ->get('/admin/role-templates')
        ->assertOk();
});

it('denies users without view_assignments permission from role templates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/role-templates')
        ->assertForbidden();
});

it('allows admin to access assignment wizard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin/assignment-wizard')
        ->assertOk();
});

it('allows user with manage_assignments permission to access wizard', function () {
    $user = User::factory()->create();
    $user->assignRole('Treasurer');
    $user->givePermissionTo('manage_assignments');

    $this->actingAs($user)
        ->get('/admin/assignment-wizard')
        ->assertOk();
});

it('denies user without manage_assignments from accessing wizard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/assignment-wizard')
        ->assertForbidden();
});
