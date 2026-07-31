<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\QuestionBankQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamQuestionFactory extends Factory
{
    protected $model = ExamQuestion::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'question_bank_question_id' => QuestionBankQuestion::factory(),
            'custom_marks' => fake()->optional(0.3)->randomElement([5, 10, 15, 20]),
            'order' => fake()->numberBetween(0, 50),
        ];
    }
}
