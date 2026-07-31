<?php

use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\AssignmentRole;
use App\Models\RoleTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('creates assignment with fillable attributes', function () {
    $user = User::factory()->create();

    $assignment = Assignment::factory()->create([
        'name' => 'Test Assignment',
        'created_by' => $user->id,
    ]);

    expect($assignment->name)->toBe('Test Assignment')
        ->and($assignment->creator->id)->toBe($user->id);
});

it('has roles relationship', function () {
    $assignment = Assignment::factory()
        ->has(AssignmentRole::factory()->count(3), 'roles')
        ->create();

    expect($assignment->roles)->toHaveCount(3);
});

it('has members through roles', function () {
    $assignment = Assignment::factory()->create();
    $role = AssignmentRole::factory()->create(['assignment_id' => $assignment->id]);
    AssignmentMember::factory()->count(2)->create(['assignment_role_id' => $role->id]);

    expect($assignment->members)->toHaveCount(2);
});

it('computes total seats needed', function () {
    $assignment = Assignment::factory()->create();
    AssignmentRole::factory()->create(['assignment_id' => $assignment->id, 'seats_required' => 3]);
    AssignmentRole::factory()->create(['assignment_id' => $assignment->id, 'seats_required' => 2]);

    expect($assignment->totalSeatsNeeded)->toBe(5);
});

it('computes total seats filled', function () {
    $assignment = Assignment::factory()->create();
    $role = AssignmentRole::factory()->create(['assignment_id' => $assignment->id, 'seats_required' => 3, 'seats_filled' => 2]);

    expect($assignment->totalSeatsFilled)->toBe(2);
});

it('has assignmentRoles on RoleTemplate', function () {
    $template = RoleTemplate::factory()->create();
    $assignment = Assignment::factory()->create();
    AssignmentRole::factory()->create([
        'assignment_id' => $assignment->id,
        'role_template_id' => $template->id,
    ]);

    expect($template->assignmentRoles)->toHaveCount(1)
        ->and($template->assignmentRoles->first()->assignment_id)->toBe($assignment->id);
});

it('casts attributes correctly', function () {
    $assignment = Assignment::factory()->create([
        'policy_weights' => ['skill_weight' => 50, 'fairness_weight' => 50],
        'deadline' => now()->addDays(7),
    ]);

    expect($assignment->policy_weights)->toBeArray()
        ->and($assignment->deadline)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('has approved members scope on assignment role', function () {
    $role = AssignmentRole::factory()->create();
    AssignmentMember::factory()->create(['assignment_role_id' => $role->id, 'status' => 'approved']);
    AssignmentMember::factory()->create(['assignment_role_id' => $role->id, 'status' => 'suggested']);

    expect($role->approvedMembers)->toHaveCount(1);
});

it('computes skills remaining', function () {
    $role = AssignmentRole::factory()->create(['seats_required' => 5, 'seats_filled' => 3]);

    expect($role->skillsRemaining)->toBe(2);
});
