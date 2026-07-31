<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'target_type' => fake()->randomElement(['project', 'event', 'committee', 'custom']),
            'target_id' => null,
            'deadline' => fake()->optional()->dateTimeBetween('+1 week', '+3 months'),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => fake()->randomElement(['draft', 'generating', 'pending_review', 'approved']),
            'confidence_score' => fake()->optional()->randomFloat(2, 0, 100),
            'fairness_score' => fake()->optional()->randomFloat(2, 0, 100),
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
            'context_notes' => fake()->optional()->text(),
            'created_by' => User::factory(),
        ];
    }
}
