<?php

namespace App\Services;

use App\Events\AssignmentApproved;
use App\Events\AssignmentGenerated;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\AssignmentRole;
use App\Models\User;
use App\Notifications\AssignmentApprovedNotification;
use App\Notifications\AssignmentGeneratedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function createAssignment(array $data): Assignment
    {
        return DB::transaction(function () use ($data) {
            $policyWeights = $data['policy_weights'] ?? [
                'skill_weight' => 40,
                'fairness_weight' => 25,
                'workload_weight' => 20,
                'experience_weight' => 15,
                'skill_enabled' => true,
                'fairness_enabled' => true,
                'workload_enabled' => true,
                'experience_enabled' => true,
            ];

            $assignment = Assignment::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'target_type' => $data['target_type'] ?? 'custom',
                'target_id' => $data['target_id'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'draft',
                'policy_weights' => $policyWeights,
                'context_notes' => $data['context_notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            foreach ($data['roles'] as $index => $roleData) {
                $assignment->roles()->create([
                    'role_template_id' => $roleData['role_template_id'] ?? null,
                    'name' => $roleData['name'],
                    'seats_required' => $roleData['seats'] ?? 1,
                    'seats_filled' => 0,
                    'required_skills' => $roleData['skills'] ?? [],
                    'is_lead_required' => $roleData['lead_required'] ?? false,
                    'sort_order' => $index,
                ]);
            }

            return $assignment->fresh('roles');
        });
    }

    public function generateAssignments(Assignment $assignment): Assignment
    {
        $assignment->update(['status' => 'generating']);

        $weights = $assignment->policy_weights;
        $candidates = User::where('membership_status', 'active')->get();

        foreach ($assignment->roles as $role) {
            $scoredMembers = $this->scoreMembersForRole($role, $candidates, $weights);

            $assigned = array_slice($scoredMembers, 0, $role->seats_required);
            $backups = array_slice($scoredMembers, $role->seats_required, $role->seats_required);

            $role->members()->delete();

            foreach ($assigned as $index => $member) {
                $role->members()->create([
                    'user_id' => $member['user_id'],
                    'is_lead' => $member['is_lead'],
                    'is_backup' => false,
                    'confidence_score' => $member['confidence_score'],
                    'reasoning' => $member['reasoning'],
                    'conflict_flags' => $member['conflicts'],
                    'status' => 'suggested',
                    'sort_order' => $index,
                ]);
            }

            foreach ($backups as $index => $member) {
                $role->members()->create([
                    'user_id' => $member['user_id'],
                    'is_lead' => false,
                    'is_backup' => true,
                    'confidence_score' => $member['confidence_score'],
                    'reasoning' => $member['reasoning'],
                    'conflict_flags' => $member['conflicts'],
                    'status' => 'suggested',
                    'sort_order' => $index + $role->seats_required,
                ]);
            }

            $role->update([
                'seats_filled' => count($assigned),
            ]);
        }

        $assignment = $assignment->fresh(['roles.members.user']);

        $stats = $this->calculateScores($assignment);
        $assignment->update([
            'status' => 'pending_review',
            'confidence_score' => $stats['confidence_score'],
            'fairness_score' => $stats['fairness_score'],
        ]);

        $assignment = $assignment->fresh(['roles.members.user']);

        $totalAssigned = $assignment->roles->sum(fn ($role) => $role->members->count());

        AssignmentGenerated::dispatch($assignment, $totalAssigned);

        $creator = $assignment->creator;
        if ($creator) {
            $creator->notify(new AssignmentGeneratedNotification($assignment));
        }

        return $assignment;
    }

    public function approveAssignment(Assignment $assignment): Assignment
    {
        $assignment->roles->each(function (AssignmentRole $role) {
            $role->members()->where('status', 'suggested')->update(['status' => 'approved']);
            $role->update([
                'seats_filled' => $role->members()->where('status', 'approved')->count(),
            ]);
        });

        $assignment->update(['status' => 'approved']);

        $assignment = $assignment->fresh(['roles.members.user']);

        AssignmentApproved::dispatch($assignment, auth()->id());

        $creator = $assignment->creator;
        if ($creator) {
            $creator->notify(new AssignmentApprovedNotification($assignment));
        }

        return $assignment;
    }

    public function moveMember(AssignmentMember $member, AssignmentRole $targetRole): void
    {
        $member->update([
            'assignment_role_id' => $targetRole->id,
        ]);

        $this->recalculateRoleCounts($member->role->assignment);
    }

    public function recalculateRoleCounts(Assignment $assignment): void
    {
        foreach ($assignment->roles as $role) {
            $role->update([
                'seats_filled' => $role->members()->where('status', 'approved')->count(),
            ]);
        }
    }

    /**
     * @return array<int, array{user_id: int, is_lead: bool, confidence_score: float, reasoning: string, conflicts: array}>
     */
    private function scoreMembersForRole(AssignmentRole $role, Collection $candidates, array $weights): array
    {
        $scored = [];

        foreach ($candidates as $candidate) {
            $score = 0;
            $components = [];
            $conflicts = [];

            if ($weights['skill_enabled']) {
                $skillMatch = $this->calculateSkillMatch($role->required_skills ?? [], $candidate);
                $score += $skillMatch * ($weights['skill_weight'] / 100);
                $components['skill'] = $skillMatch;
            }

            if ($weights['fairness_enabled']) {
                $fairness = $this->calculateFairness($candidate);
                $score += $fairness * ($weights['fairness_weight'] / 100);
                $components['fairness'] = $fairness;
            }

            if ($weights['workload_enabled']) {
                $workloadScore = $this->calculateWorkloadScore($candidate);
                $score += $workloadScore * ($weights['workload_weight'] / 100);
                $components['workload'] = $workloadScore;
            }

            if ($weights['experience_enabled']) {
                $experience = $this->calculateExperience($candidate);
                $score += $experience * ($weights['experience_weight'] / 100);
                $components['experience'] = $experience;
            }

            $normalizedScore = round(min($score / 100, 1) * 100, 2);

            if ($candidate->attendance_count > 0 && $candidate->attendance_count < 3) {
                $conflicts[] = 'low_attendance';
            }

            $reasoning = $this->buildReasoning($components, $role, $candidate, $normalizedScore);

            $scored[] = [
                'user_id' => $candidate->id,
                'is_lead' => $role->is_lead_required && $this->hasLeadershipExperience($candidate),
                'confidence_score' => $normalizedScore,
                'reasoning' => $reasoning,
                'conflicts' => $conflicts,
            ];
        }

        usort($scored, fn ($a, $b) => $b['confidence_score'] <=> $a['confidence_score']);

        return $scored;
    }

    private function calculateSkillMatch(array $requiredSkills, User $candidate): float
    {
        if (empty($requiredSkills)) {
            return 0.7;
        }

        $memberSkills = $this->getUserSkills($candidate);

        if (empty($memberSkills)) {
            return 0.1;
        }

        $normalizedRequired = array_map('strtolower', $requiredSkills);
        $normalizedMember = array_map('strtolower', $memberSkills);

        $matches = array_intersect($normalizedRequired, $normalizedMember);

        if (count($requiredSkills) === 0) {
            return 0;
        }

        return count($matches) / count($requiredSkills);
    }

    private function calculateFairness(User $candidate): float
    {
        $roleAssignments = AssignmentMember::where('user_id', $candidate->id)
            ->where('status', 'approved')
            ->count();

        $totalAssignments = Assignment::where('status', 'approved')->count();

        if ($totalAssignments === 0) {
            return 0.8;
        }

        $fairShare = max(0, 1 - ($roleAssignments / max($totalAssignments, 1)));

        $attendanceBonus = min($candidate->attendance_count / 20, 0.2);

        return min($fairShare + $attendanceBonus, 1.0);
    }

    private function calculateWorkloadScore(User $candidate): float
    {
        $activeAssignments = AssignmentMember::where('user_id', $candidate->id)
            ->whereIn('status', ['approved', 'suggested'])
            ->count();

        $existing = max(0, 3 - $activeAssignments);
        $workloadScore = $existing / 3;

        return max(0, min($workloadScore, 1.0));
    }

    private function calculateExperience(User $candidate): float
    {
        $score = 0;

        if ($candidate->score && $candidate->score > 0) {
            $score += min($candidate->score / 1000, 0.4);
        }

        $score += min($candidate->attendance_count / 30, 0.3);

        $rankBonus = match ($candidate->rank) {
            'senior', 'executive' => 0.2,
            'member' => 0.1,
            default => 0,
        };
        $score += $rankBonus;

        $score += $candidate->events_attended > 10 ? 0.1 : ($candidate->events_attended > 5 ? 0.05 : 0);

        return min($score, 1.0);
    }

    private function hasLeadershipExperience(User $candidate): bool
    {
        return $candidate->rank === 'executive'
            || $candidate->rank === 'senior'
            || $candidate->membership_type === 'executive';
    }

    /**
     * @return array<int, string>
     */
    private function getUserSkills(User $user): array
    {
        $skills = [];

        if ($user->notable_problems_solved) {
            $skills[] = 'problem_solving';
        }

        if ($user->rank === 'executive' || $user->rank === 'senior') {
            $skills[] = 'leadership';
        }

        if ($user->program) {
            $programSkills = match (true) {
                str_contains(strtolower($user->program), 'computer') => ['programming', 'algorithms', 'web'],
                str_contains(strtolower($user->program), 'design') => ['design', 'ui', 'ux'],
                str_contains(strtolower($user->program), 'business') => ['management', 'communication'],
                default => ['communication'],
            };
            $skills = array_merge($skills, $programSkills);
        }

        if ($user->competition_rank) {
            $skills[] = 'competitive';
        }

        if ($user->github_username) {
            $skills[] = 'development';
        }

        return array_unique($skills);
    }

    private function buildReasoning(array $components, AssignmentRole $role, User $candidate, float $score): string
    {
        $parts = [];

        if (isset($components['skill']) && $components['skill'] > 0.5) {
            $parts[] = 'Strong skill match for '.$role->name;
        }

        if (isset($components['fairness']) && $components['fairness'] > 0.7) {
            $parts[] = 'Balanced workload distribution';
        }

        if (isset($components['experience']) && $components['experience'] > 0.5) {
            $parts[] = 'Senior level experience';
        }

        if (empty($parts)) {
            $parts[] = 'Fits '.$role->name.' requirements';
        }

        return implode('; ', $parts);
    }

    /**
     * @return array{confidence_score: float, fairness_score: float}
     */
    private function calculateScores(Assignment $assignment): array
    {
        $allScores = $assignment->roles->flatMap->members->pluck('confidence_score')->filter();

        if ($allScores->isEmpty()) {
            return ['confidence_score' => 0, 'fairness_score' => 0];
        }

        $confidenceScore = round($allScores->avg(), 2);

        $userIdScores = [];
        foreach ($assignment->roles as $role) {
            foreach ($role->members as $member) {
                if (! isset($userIdScores[$member->user_id])) {
                    $userIdScores[$member->user_id] = [];
                }
                $userIdScores[$member->user_id][] = $member->confidence_score ?? 0;
            }
        }

        $standardDeviations = [];
        foreach ($userIdScores as $scores) {
            $avg = array_sum($scores) / count($scores);
            $variance = 0;
            foreach ($scores as $s) {
                $variance += ($s - $avg) ** 2;
            }
            $standardDeviations[] = sqrt($variance / count($scores));
        }

        $fairnessScore = 100;
        if (! empty($standardDeviations)) {
            $avgDeviation = array_sum($standardDeviations) / count($standardDeviations);
            $fairnessScore = round(max(0, 100 - ($avgDeviation * 10)), 2);
        }

        return [
            'confidence_score' => $confidenceScore,
            'fairness_score' => $fairnessScore,
        ];
    }
}
