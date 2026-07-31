<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetCategory>
 */
class BudgetCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);
        $categories = $type === 'income'
            ? ['Membership Dues', 'Donations', 'Sponsorships', 'Fundraising', 'Other Income']
            : ['Events', 'Equipment', 'Prizes', 'Refreshments', 'Printing', 'Travel', 'Other Expense'];

        return [
            'name' => fake()->randomElement($categories),
            'type' => $type,
            'allocated_amount' => fake()->randomFloat(2, 100, 5000),
            'semester' => fake()->randomElement(['Fall', 'Spring']),
            'academic_year' => fake()->randomElement(['2024-2025', '2025-2026']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'name' => fake()->randomElement(['Membership Dues', 'Donations', 'Sponsorships', 'Fundraising', 'Other Income']),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'name' => fake()->randomElement(['Events', 'Equipment', 'Prizes', 'Refreshments', 'Printing', 'Travel', 'Other Expense']),
        ]);
    }
}
