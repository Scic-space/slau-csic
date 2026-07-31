<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'submitted_at' => null,
            'time_remaining_seconds' => fn (array $attrs) => $attrs['submitted_at'] ? 0 : fake()->numberBetween(600, 3600),
            'total_score' => 0,
            'passed' => false,
            'admin_notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'time_remaining_seconds' => 0,
            'total_score' => fake()->numberBetween(0, 100),
            'passed' => fake()->boolean(60),
        ]);
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'time_remaining_seconds' => 0,
            'total_score' => fake()->numberBetween(60, 100),
            'passed' => true,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'time_remaining_seconds' => 0,
            'total_score' => fake()->numberBetween(0, 49),
            'passed' => false,
        ]);
    }
}
