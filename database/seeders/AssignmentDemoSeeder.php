<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\AssignmentRole;
use App\Models\RoleTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@slau-csic.org')->first();

        if (! $admin) {
            $this->command->warn('Admin user not found — skipping assignment demo seeder.');

            return;
        }

        $templates = [
            ['name' => 'Team Lead', 'category' => 'leadership', 'required_skills' => ['Leadership', 'Communication', 'Project Management'], 'min_experience' => 'advanced', 'sort_order' => 1],
            ['name' => 'Developer', 'category' => 'technical', 'required_skills' => ['PHP', 'JavaScript', 'Development'], 'min_experience' => 'intermediate', 'sort_order' => 2],
            ['name' => 'Designer', 'category' => 'technical', 'required_skills' => ['Design', 'UI/UX', 'Communication'], 'min_experience' => 'intermediate', 'sort_order' => 3],
            ['name' => 'Content Writer', 'category' => 'general', 'required_skills' => ['Writing', 'Communication'], 'min_experience' => 'beginner', 'sort_order' => 4],
        ];

        $createdTemplates = [];
        foreach ($templates as $tpl) {
            $createdTemplates[] = RoleTemplate::firstOrCreate(
                ['name' => $tpl['name']],
                $tpl
            );
        }

        $this->command->info('Created/verified '.count($createdTemplates).' role templates.');

        $existingAssignment = Assignment::where('name', 'Hackathon 2026 Team Formation')->first();
        if ($existingAssignment) {
            $this->command->info('Demo assignment already exists — skipping.');

            return;
        }

        $assignment = Assignment::create([
            'name' => 'Hackathon 2026 Team Formation',
            'description' => 'Auto-generated teams for the annual hackathon. Assigns developers, designers, and team leads across 5 teams.',
            'target_type' => 'event',
            'target_id' => null,
            'deadline' => now()->addDays(14),
            'priority' => 'high',
            'status' => 'draft',
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
            'context_notes' => 'Teams should be balanced in skill level. Ensure each team has at least one senior developer.',
            'created_by' => $admin->id,
        ]);

        $roles = [
            ['name' => 'Team Lead', 'seats_required' => 5, 'required_skills' => ['Leadership', 'Communication', 'Project Management'], 'is_lead_required' => false, 'sort_order' => 0],
            ['name' => 'Developer', 'seats_required' => 10, 'required_skills' => ['PHP', 'JavaScript', 'Development'], 'is_lead_required' => false, 'sort_order' => 1],
            ['name' => 'Designer', 'seats_required' => 5, 'required_skills' => ['Design', 'UI/UX'], 'is_lead_required' => false, 'sort_order' => 2],
        ];

        foreach ($roles as $rData) {
            AssignmentRole::create([
                'assignment_id' => $assignment->id,
                'name' => $rData['name'],
                'seats_required' => $rData['seats_required'],
                'seats_filled' => 0,
                'required_skills' => $rData['required_skills'],
                'is_lead_required' => $rData['is_lead_required'],
                'sort_order' => $rData['sort_order'],
            ]);
        }

        if ($student = User::where('email', 'student@slau-csic.org')->first()) {
            $devRole = AssignmentRole::where('assignment_id', $assignment->id)->where('name', 'Developer')->first();
            if ($devRole) {
                AssignmentMember::create([
                    'assignment_role_id' => $devRole->id,
                    'user_id' => $student->id,
                    'is_lead' => false,
                    'is_backup' => false,
                    'confidence_score' => 82.5,
                    'reasoning' => 'Computer Science student with strong problem-solving skills',
                    'conflict_flags' => [],
                    'status' => 'suggested',
                    'sort_order' => 0,
                ]);
                $devRole->update(['seats_filled' => 1]);
            }
        }

        $this->command->info('Demo assignment created with draft status.');
    }
}
