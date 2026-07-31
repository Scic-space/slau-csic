<?php

namespace Database\Factories;

use App\Models\QuestionBankQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionBankQuestionFactory extends Factory
{
    protected $model = QuestionBankQuestion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['multiple_choice', 'true_false', 'short_answer', 'code_snippet']),
            'question_text' => fake()->sentence(),
            'code_block' => null,
            'code_language' => null,
            'marks' => fake()->randomElement([5, 10, 10, 15, 20]),
            'explanation' => fake()->optional(0.7)->paragraph(),
        ];
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple_choice',
        ])->afterCreating(function (QuestionBankQuestion $question) {
            $correctIndex = fake()->numberBetween(0, 3);
            $options = [
                fake()->sentence(4),
                fake()->sentence(4),
                fake()->sentence(4),
                fake()->sentence(4),
            ];

            foreach ($options as $i => $text) {
                $question->options()->create([
                    'option_text' => $text,
                    'is_correct' => $i === $correctIndex,
                    'order' => $i,
                ]);
            }
        });
    }

    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'true_false',
        ])->afterCreating(function (QuestionBankQuestion $question) {
            $isTrue = fake()->boolean();
            $question->options()->createMany([
                ['option_text' => 'True', 'is_correct' => $isTrue, 'order' => 0],
                ['option_text' => 'False', 'is_correct' => ! $isTrue, 'order' => 1],
            ]);
        });
    }

    public function shortAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'short_answer',
            'marks' => 10,
        ])->afterCreating(function (QuestionBankQuestion $question) {
            $question->options()->create([
                'option_text' => fake()->sentence(),
                'is_correct' => true,
                'order' => 0,
            ]);
        });
    }
}
