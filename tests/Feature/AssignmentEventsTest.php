<?php

use App\Events\AssignmentApproved;
use App\Events\AssignmentGenerated;
use App\Models\User;
use App\Notifications\AssignmentApprovedNotification;
use App\Notifications\AssignmentGeneratedNotification;
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->service = app(AssignmentService::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('dispatches AssignmentGenerated event when generating', function () {
    Event::fake();

    $assignment = $this->service->createAssignment([
        'name' => 'Event Test',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Role', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    User::factory()->create(['membership_status' => 'active']);

    $this->service->generateAssignments($assignment);

    Event::assertDispatched(AssignmentGenerated::class);
});

it('dispatches AssignmentApproved event when approving', function () {
    Event::fake();

    $assignment = $this->service->createAssignment([
        'name' => 'Approve Event',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Role', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $user = User::factory()->create(['membership_status' => 'active']);
    $role = $assignment->roles->first();
    $role->members()->create(['user_id' => $user->id, 'status' => 'suggested', 'sort_order' => 0]);
    $assignment->update(['status' => 'pending_review']);

    $this->service->approveAssignment($assignment->fresh());

    Event::assertDispatched(AssignmentApproved::class);
});

it('sends notification to creator when assignment is generated', function () {
    Notification::fake();

    $assignment = $this->service->createAssignment([
        'name' => 'Notify Test',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Role', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    User::factory()->create(['membership_status' => 'active']);

    $this->service->generateAssignments($assignment);

    Notification::assertSentTo(
        $this->admin,
        AssignmentGeneratedNotification::class
    );
});

it('sends notification to creator when assignment is approved', function () {
    Notification::fake();

    $assignment = $this->service->createAssignment([
        'name' => 'Notify Approve',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Role', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $user = User::factory()->create(['membership_status' => 'active']);
    $role = $assignment->roles->first();
    $role->members()->create(['user_id' => $user->id, 'status' => 'suggested', 'sort_order' => 0]);
    $assignment->update(['status' => 'pending_review']);

    $this->service->approveAssignment($assignment->fresh());

    Notification::assertSentTo(
        $this->admin,
        AssignmentApprovedNotification::class
    );
});
