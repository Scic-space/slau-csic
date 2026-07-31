<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentRole;
use App\Models\RoleTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentRole>
 */
class AssignmentRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'role_template_id' => null,
            'name' => fake()->randomElement(['Team Lead', 'Developer', 'Designer', 'Coordinator', 'Tester', 'Researcher', 'Writer', 'Analyst']),
            'seats_required' => fake()->numberBetween(1, 5),
            'seats_filled' => 0,
            'required_skills' => fake()->randomElements(['PHP', 'JavaScript', 'Python', 'Design', 'Writing', 'Leadership', 'Communication'], fake()->numberBetween(1, 3)),
            'is_lead_required' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function fromTemplate(RoleTemplate $template): static
    {
        return $this->state(fn (array $attrs) => [
            'role_template_id' => $template->id,
            'name' => $template->name,
            'required_skills' => $template->required_skills,
            'is_lead_required' => $template->approval_route === 'lead',
        ]);
    }
}
