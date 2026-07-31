<?php

use App\Filament\Pages\AssignmentWizard;
use App\Models\Assignment;
use App\Models\RoleTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
    actingAs($this->admin);
});

it('starts on step 1 with default values', function () {
    Livewire::test(AssignmentWizard::class)
        ->assertSet('step', 1)
        ->assertSet('targetType', 'custom')
        ->assertSet('roles', [['name' => '', 'seats' => 1, 'skills' => [], 'lead_required' => false]])
        ->assertSet('priority', 'medium');
});

it('navigates to next step', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test Assignment')
        ->call('nextStep')
        ->assertSet('step', 2);
});

it('navigates to previous step', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->call('prevStep')
        ->assertSet('step', 1);
});

it('blocks next step on step 1 when custom name is empty', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertSet('validationErrors.customName', 'Please provide a name for the assignment.');
});

it('clears validation errors on navigation', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('nextStep')
        ->call('clearValidationErrors')
        ->assertSet('validationErrors', []);
});

it('blocks next step on step 2 when role name is empty', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSee('Role #1 needs a name.');
});

it('blocks next step on step 2 when role seats are zero', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->set('roles.0.name', 'Developer')
        ->set('roles.0.seats', 0)
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertSee('Must have at least 1 seat.');
});

it('adds a new role', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('addRole')
        ->assertCount('roles', 2);
});

it('removes a role', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->call('addRole')
        ->call('removeRole', 1)
        ->assertCount('roles', 1);
});

it('does not remove the last role', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('removeRole', 0)
        ->assertCount('roles', 1);
});

it('moves a role up', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('addRole')
        ->set('roles.0.name', 'Second')
        ->set('roles.1.name', 'First')
        ->call('moveRoleUp', 1)
        ->assertSet('roles.0.name', 'First')
        ->assertSet('roles.1.name', 'Second');
});

it('moves a role down', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('addRole')
        ->set('roles.0.name', 'First')
        ->set('roles.1.name', 'Second')
        ->call('moveRoleDown', 0)
        ->assertSet('roles.0.name', 'Second')
        ->assertSet('roles.1.name', 'First');
});

it('does not move first role up', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('roles.0.name', 'Only')
        ->call('moveRoleUp', 0)
        ->assertSet('roles.0.name', 'Only');
});

it('does not move last role down', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('roles.0.name', 'Only')
        ->call('moveRoleDown', 0)
        ->assertSet('roles.0.name', 'Only');
});

it('applies a role template', function () {
    RoleTemplate::factory()->create([
        'name' => 'Team Lead',
        'required_skills' => ['Leadership', 'PHP'],
    ]);

    Livewire::test(AssignmentWizard::class)
        ->assertCount('roles', 1)
        ->call('applyRoleTemplate', 1)
        ->assertCount('roles', 2)
        ->assertSet('roles.1.name', 'Team Lead')
        ->assertSet('roles.1.skills', ['Leadership', 'PHP']);
});

it('goes to accessible step', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->call('goToStep', 1)
        ->assertSet('step', 1);
});

it('cannot go to step beyond furthest reached step', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('goToStep', 3)
        ->assertSet('step', 1);
});

it('cannot generate if step 1 has validation errors', function () {
    Livewire::test(AssignmentWizard::class)
        ->call('generateAndGoToReview')
        ->assertSet('step', 1);
});

it('cannot generate if step 2 has validation errors', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->call('generateAndGoToReview')
        ->assertSet('step', 2);
});

it('generates assignments and goes to review', function () {
    User::factory()->count(3)->create(['membership_status' => 'active']);

    Livewire::test(AssignmentWizard::class)
        ->set('customName', 'Test')
        ->call('nextStep')
        ->set('roles.0.name', 'Developer')
        ->call('nextStep')
        ->call('generateAndGoToReview')
        ->assertSet('step', 4)
        ->assertSet('generatedResults.status', 'pending_review');
});

it('resets target id when target type changes', function () {
    Livewire::test(AssignmentWizard::class)
        ->set('targetType', 'event')
        ->set('targetId', 5)
        ->set('targetType', 'custom')
        ->assertSet('targetId', null);
});

it('loads existing draft assignment', function () {
    $assignment = Assignment::factory()->create([
        'name' => 'Draft Assignment',
        'target_type' => 'custom',
        'status' => 'draft',
        'priority' => 'high',
    ]);

    Livewire::test(AssignmentWizard::class, ['assignment' => $assignment->id])
        ->assertSet('customName', 'Draft Assignment')
        ->assertSet('priority', 'high')
        ->assertSet('assignmentId', $assignment->id);
});

it('completes full wizard flow', function () {
    $assignment = Assignment::factory()->create([
        'name' => 'Full Flow',
        'target_type' => 'custom',
        'status' => 'draft',
        'deadline' => now()->addDays(7),
        'priority' => 'high',
        'policy_weights' => [
            'skill_weight' => 40,
            'fairness_weight' => 25,
            'workload_weight' => 20,
            'experience_weight' => 15,
            'skill_enabled' => true,
            'fairness_enabled' => true,
            'workload_enabled' => true,
            'experience_enabled' => true,
        ],
        'created_by' => $this->admin->id,
    ]);

    Livewire::test(AssignmentWizard::class, ['assignment' => $assignment->id])
        ->assertSet('assignmentId', $assignment->id)
        ->assertSet('customName', 'Full Flow');
});
