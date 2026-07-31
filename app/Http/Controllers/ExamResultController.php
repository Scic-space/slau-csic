<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamResultController extends Controller
{
    public function __invoke(Request $request, ExamAttempt $attempt): Response
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);

        $attempt->load(['exam', 'answers.examQuestion.question.options']);

        $answers = $attempt->answers->map(fn ($a) => [
            'id' => $a->id,
            'question_id' => $a->exam_question_id,
            'answer_text' => $a->answer_text,
            'selected_option_id' => $a->selected_option_id,
            'is_correct' => $a->is_correct,
            'marks_awarded' => $a->marks_awarded,
            'question' => [
                'question_text' => $a->examQuestion->question->question_text,
                'image' => $a->examQuestion->question->image ? \Illuminate\Support\Facades\Storage::url($a->examQuestion->question->image) : null,
                'type' => $a->examQuestion->question->type,
                'marks' => $a->examQuestion->question->marks,
                'code_block' => $a->examQuestion->question->code_block,
                'code_language' => $a->examQuestion->question->code_language,
                'explanation' => $a->examQuestion->question->explanation,
                'options' => $a->examQuestion->question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'is_correct' => $o->is_correct,
                    'order' => $o->order,
                ]),
            ],
        ]);

        return Inertia::render('exams/Result', [
            'attempt' => [
                'id' => $attempt->id,
                'total_score' => $attempt->total_score,
                'passed' => $attempt->passed,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            ],
            'exam' => [
                'id' => $attempt->exam->id,
                'title' => $attempt->exam->title,
                'description' => $attempt->exam->description,
                'passing_score' => $attempt->exam->passing_score,
                'duration_minutes' => $attempt->exam->duration_minutes,
            ],
            'answers' => $answers,
        ]);
    }
}
