<?php

namespace Database\Factories;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamAnswerFactory extends Factory
{
    protected $model = ExamAnswer::class;

    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'exam_question_id' => ExamQuestion::factory(),
            'answer_text' => fake()->optional(0.6)->sentence(),
            'selected_option_id' => null,
            'is_correct' => null,
            'marks_awarded' => 0,
        ];
    }

    public function correct(int $marks = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
            'marks_awarded' => $marks,
        ]);
    }

    public function incorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
            'marks_awarded' => 0,
        ]);
    }

    public function pendingGrading(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => null,
            'marks_awarded' => 0,
        ]);
    }
}
