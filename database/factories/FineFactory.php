<?php

namespace Database\Factories;

use App\Models\FineType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fine>
 */
class FineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fine_type_id' => FineType::factory(),
            'amount' => fake()->randomFloat(2, 2, 50),
            'reason' => fake()->sentence(),
            'issue_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+14 days'),
            'status' => fake()->randomElement(['pending', 'paid', 'partially_paid', 'waived']),
            'amount_paid' => 0,
            'issued_by' => User::factory(),
            'waived_by' => null,
            'waived_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'amount_paid' => 0,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'amount_paid' => $attributes['amount'],
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partially_paid',
            'amount_paid' => fake()->randomFloat(2, 1, $attributes['amount'] - 1),
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waived',
            'waived_by' => User::factory(),
            'waived_reason' => fake()->sentence(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
