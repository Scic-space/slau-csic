<?php

use App\Models\Assignment;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->service = app(AssignmentService::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('creates an assignment with roles', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Test Hackathon',
        'description' => 'Test description',
        'target_type' => 'custom',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Developer', 'seats' => 3, 'skills' => ['PHP', 'JavaScript'], 'lead_required' => false],
            ['name' => 'Designer', 'seats' => 2, 'skills' => ['Design'], 'lead_required' => false],
        ],
    ]);

    expect($assignment)->toBeInstanceOf(Assignment::class)
        ->and($assignment->name)->toBe('Test Hackathon')
        ->and($assignment->status)->toBe('draft')
        ->and($assignment->roles)->toHaveCount(2)
        ->and($assignment->totalSeatsNeeded)->toBe(5);
});

it('generates assignments for roles', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Generate Test',
        'description' => 'Test generation',
        'target_type' => 'custom',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Developer', 'seats' => 2, 'skills' => ['Leadership'], 'lead_required' => false],
        ],
    ]);

    User::factory()->count(3)->create([
        'membership_status' => 'active',
        'rank' => 'silver',
        'score' => 100,
    ]);

    $generated = $this->service->generateAssignments($assignment);

    expect($generated->status)->toBe('pending_review')
        ->and($generated->roles->first()->members)->toHaveCount(3);
});

it('approves an assignment and marks members as approved', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Approve Test',
        'description' => 'Test approval',
        'target_type' => 'custom',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Developer', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $users = User::factory()->count(2)->create(['membership_status' => 'active']);

    $role = $assignment->roles->first();
    $role->members()->create([
        'user_id' => $users[0]->id,
        'status' => 'suggested',
        'sort_order' => 0,
    ]);

    $assignment->update(['status' => 'pending_review']);

    $approved = $this->service->approveAssignment($assignment->fresh());

    expect($approved->status)->toBe('approved');
    expect($approved->roles->first()->members->first()->status)->toBe('approved');
});

it('moves a member between roles', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Move Test',
        'description' => 'Test member move',
        'target_type' => 'custom',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Dev', 'seats' => 2, 'skills' => [], 'lead_required' => false],
            ['name' => 'Design', 'seats' => 2, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $user = User::factory()->create(['membership_status' => 'active']);
    $devRole = $assignment->roles->firstWhere('name', 'Dev');
    $designRole = $assignment->roles->firstWhere('name', 'Design');

    $member = $devRole->members()->create([
        'user_id' => $user->id,
        'status' => 'approved',
        'sort_order' => 0,
    ]);

    $this->service->moveMember($member, $designRole);

    expect($member->fresh()->assignment_role_id)->toBe($designRole->id);
});

it('creates assignment with default policy weights when none provided', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Default Weights',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Role', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $weights = $assignment->policy_weights;

    expect($weights['skill_weight'])->toBe(40)
        ->and($weights['fairness_weight'])->toBe(25)
        ->and($weights['workload_weight'])->toBe(20)
        ->and($weights['experience_weight'])->toBe(15)
        ->and($weights['skill_enabled'])->toBeTrue();
});

it('recalculates role counts after moving a member', function () {
    $assignment = $this->service->createAssignment([
        'name' => 'Recount Test',
        'created_by' => $this->admin->id,
        'roles' => [
            ['name' => 'Src', 'seats' => 2, 'skills' => [], 'lead_required' => false],
            ['name' => 'Dest', 'seats' => 2, 'skills' => [], 'lead_required' => false],
        ],
    ]);

    $user = User::factory()->create(['membership_status' => 'active']);
    $srcRole = $assignment->roles->firstWhere('name', 'Src');
    $destRole = $assignment->roles->firstWhere('name', 'Dest');

    $member = $srcRole->members()->create([
        'user_id' => $user->id,
        'status' => 'approved',
        'sort_order' => 0,
    ]);

    $srcRole->update(['seats_filled' => 1]);
    $destRole->update(['seats_filled' => 0]);

    $this->service->moveMember($member, $destRole);

    expect($srcRole->fresh()->seats_filled)->toBe(0)
        ->and($destRole->fresh()->seats_filled)->toBe(1);
});
