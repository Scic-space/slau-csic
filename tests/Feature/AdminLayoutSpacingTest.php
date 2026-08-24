<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

it('aligns the admin dashboard container and gutters with the user dashboard', function () {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSee('fi-width-screen-2xl')
        ->assertSee('.fi-main {', escape: false)
        ->assertSee('padding-inline: 1.5rem', escape: false)
        ->assertSee('.fi-page-header-main-ctn {', escape: false)
        ->assertSee('.fi-page .fi-grid-layout.fi-sc-has-gap {', escape: false);
});

it('applies the same shared spacing to admin resource pages', function () {
    $this->actingAs($this->admin)
        ->get('/admin/users')
        ->assertSuccessful()
        ->assertSee('fi-width-screen-2xl')
        ->assertSee('.fi-page-content {', escape: false);
});
