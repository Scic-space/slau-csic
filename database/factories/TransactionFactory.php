<?php

namespace Database\Factories;

use App\Models\BudgetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            ? BudgetCategory::getIncomeCategories()
            : BudgetCategory::getExpenseCategories();

        return [
            'type' => $type,
            'category' => fake()->randomElement($categories),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
            'description' => fake()->sentence(),
            'receipt_path' => 'receipts/'.fake()->uuid().'.pdf',
            'paid_to_from' => fake()->name(),
            'payment_method' => fake()->randomElement(['cash', 'check', 'card', 'transfer', 'other']),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'requires_approval' => fake()->boolean(30), // 30% chance of requiring approval
            'approved_by' => null,
            'approved_at' => null,
            'created_by' => 1, // Will be overridden in tests
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category' => fake()->randomElement(BudgetCategory::getIncomeCategories()),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category' => fake()->randomElement(BudgetCategory::getExpenseCategories()),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'requires_approval' => true,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}
