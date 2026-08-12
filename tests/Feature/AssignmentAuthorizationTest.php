<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('allows super-admin to view assignments page', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get('/admin/assignments')
        ->assertOk();
});

it('allows user with view_assignments permission to view assignments', function () {
    $user = User::factory()->create();
    $user->assignRole('President');

    $this->actingAs($user)
        ->get('/admin/assignments')
        ->assertOk();
});

it('denies users without view_assignments permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/assignments')
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

it('allows user with view_assignments to view role templates', function () {
    $user = User::factory()->create();
    $user->assignRole('President');

    $this->actingAs($user)
        ->get('/admin/role-templates')
        ->assertOk();
});

it('denies user without view_assignments from role templates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/role-templates')
        ->assertForbidden();
});
