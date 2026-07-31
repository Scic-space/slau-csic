<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;

class ExamGradingService
{
    public function __construct(
        protected AiGradingService $aiGradingService,
    ) {}

    public function gradeAttempt(ExamAttempt $attempt): array
    {
        $totalScore = 0;
        $passed = false;
        $answers = [];

        foreach ($attempt->answers as $answer) {
            $question = $answer->examQuestion?->question;

            if (! $question) {
                continue;
            }

            switch ($question->type) {
                case 'multiple_choice':
                case 'true_false':
                case 'code_snippet':
                    $this->gradeMcq($answer);
                    break;
                case 'short_answer':
                    $this->gradeShortAnswer($answer);
                    break;
            }

            $totalScore += $answer->marks_awarded ?? 0;
            $answers[] = [
                'question_id' => $answer->exam_question_id,
                'marks_awarded' => $answer->marks_awarded,
                'is_correct' => $answer->is_correct,
            ];
        }

        // Calculate pass/fail
        $totalQuestions = $attempt->answers->count();
        $totalMarks = $attempt->exam->questions->sum(fn ($eq) => $eq->effective_marks);
        $passed = $totalMarks > 0 ? ($totalScore / $totalMarks) >= ($attempt->exam->passing_score / 100) : false;

        $attempt->update([
            'total_score' => $totalScore,
            'passed' => $passed,
        ]);

        return [
            'total_score' => $totalScore,
            'passed' => $passed,
            'answers' => $answers,
        ];
    }

    public function gradeMcq(ExamAnswer $answer): void
    {
        $question = $answer->examQuestion?->question;
        $correctOption = $question?->options()->where('is_correct', true)->first();

        $isCorrect = $correctOption && $answer->selected_option_id == $correctOption->id;
        $marksAwarded = $isCorrect ? $answer->examQuestion->effective_marks : 0;

        $answer->update([
            'is_correct' => $isCorrect,
            'marks_awarded' => $marksAwarded,
        ]);
    }

    public function gradeShortAnswer(ExamAnswer $answer): void
    {
        $question = $answer->examQuestion?->question;

        if (! $question) {
            return;
        }

        // If AI grading is enabled
        if (config('exam.grading.ai_enabled')) {
            $result = $this->aiGradingService->gradeShortAnswer(
                $question->question_text,
                $question->options->where('is_correct', true)->first()?->option_text ?? '',
                $answer->answer_text ?? '',
                $question->explanation
            );

            $score = $result['score'] ?? 0;
            $isCorrect = $score > 0.5;
            $marksAwarded = (int) round($answer->examQuestion->effective_marks * $score);

            $answer->update([
                'is_correct' => $isCorrect,
                'marks_awarded' => $marksAwarded,
            ]);
        } else {
            // Manual grading needed
            $answer->update([
                'is_correct' => null,
                'marks_awarded' => 0,
            ]);
        }
    }

    public function calculateTotalScore(ExamAttempt $attempt): void
    {
        $totalScore = $attempt->answers->sum('marks_awarded');
        $totalMarks = $attempt->exam->questions->sum(fn ($eq) => $eq->effective_marks);
        $passed = $totalMarks > 0 ? ($totalScore / $totalMarks) >= ($attempt->exam->passing_score / 100) : false;

        $attempt->update([
            'total_score' => $totalScore,
            'passed' => $passed,
        ]);
    }
}
