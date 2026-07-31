<?php

use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

it('records attendance via QR code', function () {
    $meeting = Meeting::factory()->ongoing()->teachingSession()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    actingAs($user);
    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => true]);

    assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'check_in_method' => 'qr_code',
    ]);
});

it('prevents duplicate attendance', function () {
    $meeting = Meeting::factory()->ongoing()->teachingSession()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    actingAs($user);
    $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response = $this->post("/attendance/verify/{$meeting->meeting_code}");

    $response->assertJson(['success' => false]);
    $count = Attendance::where('meeting_id', $meeting->id)->where('user_id', $user->id)->count();
    expect($count)->toBe(1);
});

it('rejects check-in for non-existent meeting code', function () {
    actingAs($this->admin);
    $response = $this->post('/attendance/verify/INVALID');

    $response->assertJson(['success' => false]);
    $response->assertStatus(404);
});

it('tracks check-in method', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    $meeting->recordAttendance($user, 'manual', ['marked_by' => $this->admin->id]);

    assertDatabaseHas('attendance', [
        'meeting_id' => $meeting->id,
        'user_id' => $user->id,
        'check_in_method' => 'manual',
    ]);
});

it('respects allowed attendees restriction', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $allowedUser = User::factory()->create();
    $restrictedUser = User::factory()->create();

    $meeting->allowedAttendees()->attach($allowedUser->id);

    expect($meeting->canUserAttend($allowedUser))->toBeTrue();
    expect($meeting->canUserAttend($restrictedUser))->toBeFalse();
});

it('allows all users when no restriction is set', function () {
    $meeting = Meeting::factory()->ongoing()->create(['created_by' => $this->admin->id]);
    $user = User::factory()->create();

    expect($meeting->canUserAttend($user))->toBeTrue();
});

it('computes attendance statistics correctly', function () {
    $meeting = Meeting::factory()->ongoing()->create([
        'created_by' => $this->admin->id,
        'expected_attendees' => 20,
    ]);

    $users = User::factory()->count(10)->create();

    foreach ($users as $user) {
        $meeting->recordAttendance($user, 'qr_code', [
            'status' => 'present',
        ]);
    }

    expect($meeting->getAttendanceCount())->toBe(10);
    expect($meeting->getAttendanceRate())->toBe(50.0);
    expect($meeting->getPresentCount())->toBe(10);
});
