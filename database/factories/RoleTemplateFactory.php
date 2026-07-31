<?php

namespace Database\Factories;

use App\Models\RoleTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoleTemplate>
 */
class RoleTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Team Lead', 'Developer', 'Designer', 'Coordinator', 'Tester', 'Researcher', 'Writer', 'Analyst']),
            'category' => fake()->randomElement(['technical', 'leadership', 'operations', 'general']),
            'required_skills' => fake()->randomElements(['PHP', 'JavaScript', 'Python', 'Design', 'Writing', 'Leadership', 'Communication', 'Data Analysis', 'Project Management'], fake()->numberBetween(1, 4)),
            'min_experience' => fake()->optional()->randomElement(['beginner', 'intermediate', 'advanced']),
            'availability_requirement' => fake()->optional()->randomElement(['low', 'medium', 'high']),
            'approval_route' => fake()->optional()->randomElement(['auto', 'admin', 'lead']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
