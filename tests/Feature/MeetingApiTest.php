<?php

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('member');
    actingAs($this->user);
});

it('returns upcoming meetings via API', function () {
    $admin = User::factory()->create();
    Meeting::factory()->count(3)->upcoming()->create(['created_by' => $admin->id]);
    Meeting::factory()->count(2)->past()->create(['created_by' => $admin->id]);

    $response = getJson('/api/meetings/upcoming');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

it('returns single meeting details via API', function () {
    $admin = User::factory()->create();
    $meeting = Meeting::factory()->create(['created_by' => $admin->id]);

    $response = getJson("/api/meetings/{$meeting->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $meeting->id);
    $response->assertJsonPath('data.title', $meeting->title);
});

it('validates meeting code has correct length', function () {
    $code = Meeting::generateUniqueMeetingCode();

    expect(strlen($code))->toBe(8);
});

it('generates unique meeting codes', function () {
    $codes = collect();

    for ($i = 0; $i < 10; $i++) {
        $codes->push(Meeting::generateUniqueMeetingCode());
    }

    expect($codes->unique()->count())->toBe(10);
});
