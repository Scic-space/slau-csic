<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Poll>
 */
class PollFactory extends Factory
{
    protected $model = Poll::class;

    public function definition(): array
    {
        $question = fake()->sentence(6);

        return [
            'question' => $question,
            'slug' => Str::slug($question),
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'is_published' => false,
            'allow_multiple' => false,
            'expires_at' => null,
            'votes_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'expires_at' => fake()->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'expires_at' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
        ]);
    }
}
