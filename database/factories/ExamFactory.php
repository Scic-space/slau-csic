<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Cybersecurity Fundamentals Assessment',
                'Network Security Basics',
                'Ethical Hacking Certification Prep',
                'Cloud Security Architecture',
                'Incident Response & Forensics',
                'Web Application Security',
                'Cryptography Concepts',
                'SOC Analyst Skills Test',
            ]),
            'description' => fake()->optional()->paragraph(),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 60, 90, 120]),
            'passing_score' => fake()->randomElement([50, 60, 70, 80]),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function withQuestions(int $count = 3, ?User $creator = null): static
    {
        return $this->afterCreating(function (Exam $exam) use ($count, $creator) {
            $questions = \App\Models\QuestionBankQuestion::factory($count)->create(
                $creator ? ['user_id' => $creator->id] : []
            );

            foreach ($questions as $i => $question) {
                $exam->examQuestions()->create([
                    'question_bank_question_id' => $question->id,
                    'order' => $i,
                    'custom_marks' => fake()->randomElement([null, 5, 10, 15]),
                ]);
            }
        });
    }

    public function withAttempt(int $userId, bool $completed = true): static
    {
        return $this->afterCreating(function (Exam $exam) use ($userId, $completed) {
            $attempt = $exam->attempts()->create([
                'user_id' => $userId,
                'started_at' => now()->subMinutes(30),
                'submitted_at' => $completed ? now()->subMinutes(5) : null,
                'time_remaining_seconds' => $completed ? 0 : 1800,
                'total_score' => $completed ? fake()->numberBetween(0, 100) : 0,
                'passed' => $completed ? fake()->boolean(60) : false,
            ]);

            if ($completed) {
                foreach ($exam->questions as $question) {
                    $attempt->answers()->create([
                        'exam_question_id' => $question->id,
                        'selected_option_id' => $question->question->type === 'multiple_choice'
                            ? $question->question->options()->inRandomOrder()->first()?->id
                            : null,
                        'answer_text' => $question->question->type !== 'multiple_choice'
                            ? fake()->sentence()
                            : null,
                        'is_correct' => fake()->boolean(70),
                        'marks_awarded' => fake()->numberBetween(0, $question->effective_marks),
                    ]);
                }
            }
        });
    }
}
