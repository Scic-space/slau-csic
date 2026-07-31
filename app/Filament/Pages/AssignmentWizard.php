<?php

namespace App\Filament\Pages;

use App\Models\Assignment;
use App\Models\AssignmentRole;
use App\Models\Event;
use App\Models\Project;
use App\Models\RoleTemplate;
use App\Services\AssignmentService;
use BackedEnum;
use Filament\Pages\Page;

class AssignmentWizard extends Page
{
    protected string $view = 'filament.pages.assignment-wizard';

    public array $validationErrors = [];

    public array $stepLabels = [
        1 => 'Target',
        2 => 'Roles',
        3 => 'Rules',
        4 => 'Review',
    ];

    public array $stepDescriptions = [
        1 => 'What are you staffing? Choose an event, project, or create a custom assignment.',
        2 => 'Define the roles you need to fill. Set names, seats, and required skills.',
        3 => 'Configure how AI should assign members. Weights determine which factors matter most.',
        4 => 'Review the auto-generated assignment, adjust, then approve when ready.',
    ];

    protected static ?string $title = 'Assignment Wizard';

    public static function getNavigationLabel(): string
    {
        return 'Assignment Wizard';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Assignments';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-sparkles';
    }

    public function mount(?int $assignment = null): void
    {
        if ($assignment) {
            $this->loadAssignment($assignment);
        } else {
            $this->resetWizard();
        }
    }

    public int $step = 1;

    public string $targetType = 'custom';

    public ?int $targetId = null;

    public string $customName = '';

    public string $description = '';

    public string $deadline = '';

    public string $priority = 'medium';

    public array $roles = [];

    public array $policyWeights = [
        'skill_weight' => 40,
        'fairness_weight' => 25,
        'workload_weight' => 20,
        'experience_weight' => 15,
        'skill_enabled' => true,
        'fairness_enabled' => true,
        'workload_enabled' => true,
        'experience_enabled' => true,
    ];

    public ?array $generatedResults = null;

    public ?int $assignmentId = null;

    private function resetWizard(): void
    {
        $this->step = 1;
        $this->targetType = 'custom';
        $this->targetId = null;
        $this->customName = '';
        $this->description = '';
        $this->deadline = '';
        $this->priority = 'medium';
        $this->roles = [
            ['name' => '', 'seats' => 1, 'skills' => [], 'lead_required' => false],
        ];
        $this->policyWeights = [
            'skill_weight' => 40,
            'fairness_weight' => 25,
            'workload_weight' => 20,
            'experience_weight' => 15,
            'skill_enabled' => true,
            'fairness_enabled' => true,
            'workload_enabled' => true,
            'experience_enabled' => true,
        ];
        $this->generatedResults = null;
        $this->assignmentId = null;
    }

    private function loadAssignment(int $id): void
    {
        $assignment = Assignment::with('roles.members.user')->findOrFail($id);

        $this->assignmentId = $assignment->id;
        $this->targetType = $assignment->target_type;
        $this->targetId = $assignment->target_id;

        if ($assignment->target_type === 'custom') {
            $this->customName = $assignment->name;
        }

        $this->description = $assignment->description ?? '';
        $this->deadline = $assignment->deadline?->format('Y-m-d\TH:i') ?? '';
        $this->priority = $assignment->priority;
        $this->policyWeights = $assignment->policy_weights ?? $this->policyWeights;

        $this->roles = $assignment->roles->map(fn (AssignmentRole $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'seats' => $role->seats_required,
            'skills' => $role->required_skills ?? [],
            'lead_required' => $role->is_lead_required,
        ])->toArray();

        $this->loadResultsFromAssignment($assignment);

        $this->step = $assignment->status === 'draft' ? 1 : 4;
    }

    private function loadResultsFromAssignment(Assignment $assignment): void
    {
        if ($assignment->status === 'draft' && $assignment->roles->isEmpty()) {
            return;
        }

        if ($assignment->status !== 'draft' || $assignment->roles->first()?->members->isNotEmpty()) {
            $this->generatedResults = [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'confidence_score' => $assignment->confidence_score,
                'fairness_score' => $assignment->fairness_score,
                'roles' => $assignment->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'seats_required' => $role->seats_required,
                    'seats_filled' => $role->seats_filled,
                    'members' => $role->members->map(fn ($member) => [
                        'id' => $member->id,
                        'user_id' => $member->user_id,
                        'user_name' => $member->user?->name ?? 'Unknown',
                        'is_lead' => $member->is_lead,
                        'is_backup' => $member->is_backup,
                        'confidence_score' => $member->confidence_score,
                        'reasoning' => $member->reasoning,
                        'conflict_flags' => $member->conflict_flags ?? [],
                        'status' => $member->status,
                    ])->toArray(),
                ])->toArray(),
            ];
        }
    }

    public function nextStep(): void
    {
        $this->validateStep();

        if (! empty($this->validationErrors)) {
            return;
        }

        $this->step = min($this->step + 1, 4);
        $this->clearValidationErrors();
    }

    public function prevStep(): void
    {
        $this->step = max($this->step - 1, 1);
        $this->clearValidationErrors();
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= 4 && $step <= $this->getFurthestStep()) {
            $this->step = $step;
            $this->clearValidationErrors();
        }
    }

    public function getFurthestStep(): int
    {
        if ($this->generatedResults) {
            return 4;
        }

        return min($this->step, 3);
    }

    public function generateAndGoToReview(AssignmentService $service): void
    {
        $this->validateStep();

        if (! empty($this->validationErrors)) {
            return;
        }

        $assignment = $this->saveDraftAssignment($service);

        $service->generateAssignments($assignment);

        $assignment->refresh();
        $this->loadResultsFromAssignment($assignment);

        $this->step = 4;
        $this->clearValidationErrors();
    }

    public function approve(AssignmentService $service): void
    {
        if (! $this->assignmentId) {
            return;
        }

        $assignment = Assignment::findOrFail($this->assignmentId);
        $service->approveAssignment($assignment);

        $this->loadAssignment($assignment->id);
    }

    public function moveMember(int $memberId, int $targetRoleId): void
    {
        $member = \App\Models\AssignmentMember::findOrFail($memberId);

        if ($member->role->assignment_id !== $this->assignmentId) {
            return;
        }

        $targetRole = AssignmentRole::findOrFail($targetRoleId);

        if ($targetRole->assignment_id !== $this->assignmentId) {
            return;
        }

        app(AssignmentService::class)->moveMember($member, $targetRole);

        $this->loadAssignment($this->assignmentId);
    }

    public function addRole(): void
    {
        $this->roles[] = ['name' => '', 'seats' => 1, 'skills' => [], 'lead_required' => false];
    }

    public function removeRole(int $index): void
    {
        if (count($this->roles) > 1) {
            unset($this->roles[$index]);
            $this->roles = array_values($this->roles);
        }
    }

    public function resetPolicyDefaults(): void
    {
        $this->policyWeights = [
            'skill_weight' => 40,
            'fairness_weight' => 25,
            'workload_weight' => 20,
            'experience_weight' => 15,
            'skill_enabled' => true,
            'fairness_enabled' => true,
            'workload_enabled' => true,
            'experience_enabled' => true,
        ];
    }

    public function applyRoleTemplate(int $templateId): void
    {
        $template = RoleTemplate::findOrFail($templateId);

        $this->roles[] = [
            'name' => $template->name,
            'seats' => 1,
            'skills' => $template->required_skills ?? [],
            'lead_required' => false,
        ];
    }

    public function moveRoleUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }

        $tmp = $this->roles[$index];
        $this->roles[$index] = $this->roles[$index - 1];
        $this->roles[$index - 1] = $tmp;
    }

    public function moveRoleDown(int $index): void
    {
        if ($index >= count($this->roles) - 1) {
            return;
        }

        $tmp = $this->roles[$index];
        $this->roles[$index] = $this->roles[$index + 1];
        $this->roles[$index + 1] = $tmp;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        try {
            return $user->hasPermissionTo('manage_assignments');
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
            return false;
        }
    }

    private function saveDraftAssignment(AssignmentService $service): Assignment
    {
        if ($this->assignmentId) {
            $assignment = Assignment::findOrFail($this->assignmentId);
            $assignment->roles()->delete();

            $assignment->update([
                'description' => $this->description,
                'target_type' => $this->targetType,
                'target_id' => $this->targetType !== 'custom' ? $this->targetId : null,
                'deadline' => $this->deadline ?: null,
                'priority' => $this->priority,
                'policy_weights' => $this->policyWeights,
            ]);
        } else {
            $assignment = $service->createAssignment([
                'name' => $this->targetType === 'custom' ? $this->customName : $this->getTargetName(),
                'description' => $this->description,
                'target_type' => $this->targetType,
                'target_id' => $this->targetType !== 'custom' ? $this->targetId : null,
                'deadline' => $this->deadline ?: null,
                'priority' => $this->priority,
                'policy_weights' => $this->policyWeights,
                'created_by' => auth()->id(),
                'roles' => $this->roles,
            ]);
        }

        $this->assignmentId = $assignment->id;

        return $assignment->fresh('roles');
    }

    public function getEventsProperty()
    {
        return Event::where('status', 'published')
            ->orderBy('start_date', 'desc')
            ->get(['id', 'title', 'start_date']);
    }

    public function getProjectsProperty()
    {
        return Project::orderBy('name')
            ->get(['id', 'name']);
    }

    public function getRoleTemplatesProperty()
    {
        return RoleTemplate::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function getTargetName(): string
    {
        if ($this->targetType === 'event' && $this->targetId) {
            $event = Event::find($this->targetId);

            return $event ? 'Event: '.$event->title : 'Unknown Event';
        }

        if ($this->targetType === 'project' && $this->targetId) {
            $project = Project::find($this->targetId);

            return $project ? 'Project: '.$project->name : 'Unknown Project';
        }

        return $this->customName ?: 'Untitled Assignment';
    }

    private function validateStep(): void
    {
        $this->validationErrors = [];

        if ($this->step === 1) {
            if ($this->targetType === 'custom' && empty(trim($this->customName))) {
                $this->validationErrors['customName'] = 'Please provide a name for the assignment.';

                return;
            }

            if ($this->targetType !== 'custom' && ! $this->targetId) {
                $this->validationErrors['targetId'] = 'Please select a '.$this->targetType.'.';

                return;
            }
        }

        if ($this->step === 2) {
            $hasErrors = false;

            foreach ($this->roles as $index => $role) {
                if (empty(trim($role['name'] ?? ''))) {
                    $this->validationErrors['roles.'.$index.'.name'] = 'Role #'.($index + 1).' needs a name.';
                    $hasErrors = true;
                }

                if (($role['seats'] ?? 0) < 1) {
                    $this->validationErrors['roles.'.$index.'.seats'] = 'Must have at least 1 seat.';
                    $hasErrors = true;
                }
            }

            if ($hasErrors) {
                return;
            }
        }
    }

    public function clearValidationErrors(): void
    {
        $this->validationErrors = [];
    }

    public function updatedTargetType(): void
    {
        $this->targetId = null;
    }
}
