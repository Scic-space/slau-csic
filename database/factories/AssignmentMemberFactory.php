<?php

namespace Database\Factories;

use App\Models\AssignmentMember;
use App\Models\AssignmentRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentMember>
 */
class AssignmentMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_role_id' => AssignmentRole::factory(),
            'user_id' => User::factory(),
            'is_lead' => false,
            'is_backup' => false,
            'confidence_score' => fake()->randomFloat(2, 50, 100),
            'reasoning' => fake()->optional()->sentence(),
            'conflict_flags' => fake()->optional()->randomElements(['workload_overlap', 'skill_gap', 'scheduling_conflict'], fake()->numberBetween(0, 2)),
            'status' => fake()->randomElement(['suggested', 'approved', 'rejected']),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
