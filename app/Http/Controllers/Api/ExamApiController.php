<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;

class ExamApiController extends Controller
{
    public function index()
    {
        $exams = Exam::published()
            ->withCount('questions')
            ->get()
            ->map(fn ($exam) => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score' => $exam->passing_score,
                'questions_count' => $exam->questions_count,
                'attempts_count' => $exam->attempts()->count(),
            ]);

        return response()->json(['data' => $exams]);
    }

    public function show(Exam $exam)
    {
        if ($exam->status !== 'published') {
            return response()->json(['error' => 'Exam not available'], 404);
        }

        $exam->loadCount('questions');

        return response()->json([
            'data' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score' => $exam->passing_score,
                'questions_count' => $exam->questions_count,
            ],
        ]);
    }

    public function start(Exam $exam, Request $request)
    {
        if ($exam->status !== 'published') {
            return response()->json(['error' => 'Exam not available'], 404);
        }

        $exam->load('questions.question.options');

        $existing = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('submitted_at')
            ->first();

        $mapQuestions = fn () => $exam->questions->map(fn ($eq) => [
            'id' => $eq->id,
            'question_text' => $eq->question->question_text ?? '',
            'image' => $eq->question->image ? \Illuminate\Support\Facades\Storage::url($eq->question->image) : null,
            'type' => $eq->question->type ?? 'text',
            'options' => $eq->question && $eq->question->type === 'multiple_choice'
                ? $eq->question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                ])
                : null,
            'marks' => $eq->effective_marks,
        ]);

        if ($existing) {
            return response()->json([
                'data' => [
                    'attempt_id' => $existing->id,
                    'started_at' => $existing->started_at,
                    'time_remaining_seconds' => $existing->time_remaining_seconds,
                    'questions' => $mapQuestions(),
                ],
            ]);
        }

        $attempt = app(ExamAttemptService::class)->startAttempt($exam, $request->user());

        return response()->json([
            'data' => [
                'attempt_id' => $attempt->id,
                'started_at' => $attempt->started_at,
                'time_remaining_seconds' => $attempt->time_remaining_seconds,
                'questions' => $mapQuestions(),
            ],
        ], 201);
    }

    public function submit(Request $request, ExamAttempt $attempt)
    {
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->submitted_at) {
            return response()->json(['error' => 'Already submitted'], 409);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.exam_question_id' => 'required|exists:exam_questions,id',
            'answers.*.selected_option_id' => 'nullable|exists:question_bank_options,id',
            'answers.*.text_answer' => 'nullable|string',
        ]);

        $service = app(ExamAttemptService::class);

        foreach ($validated['answers'] as $answer) {
            $question = $attempt->exam->questions()
                ->where('exam_questions.id', $answer['exam_question_id'])
                ->firstOrFail();

            $data = array_filter([
                'answer_text' => $answer['text_answer'] ?? null,
                'selected_option_id' => $answer['selected_option_id'] ?? null,
            ]);

            if (! empty($data)) {
                try {
                    $service->saveAnswer($attempt, $question, $data);
                } catch (\RuntimeException $e) {
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            }
        }

        $result = $service->submitAttempt($attempt);

        return response()->json([
            'success' => true,
            'message' => 'Exam submitted successfully',
            'attempt_id' => $attempt->id,
            'total_score' => $result['total_score'],
            'passed' => $result['passed'],
        ]);
    }

    public function result(ExamAttempt $attempt, Request $request)
    {
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt->load(['exam', 'answers.examQuestion.question.options']);

        return response()->json([
            'data' => [
                'attempt_id' => $attempt->id,
                'exam_title' => $attempt->exam->title,
                'total_score' => $attempt->total_score,
                'passed' => $attempt->passed,
                'started_at' => $attempt->started_at,
                'submitted_at' => $attempt->submitted_at,
                'answers' => $attempt->answers->map(fn ($a) => [
                    'question_text' => $a->examQuestion->question->question_text,
                    'marks_awarded' => $a->marks_awarded,
                    'is_correct' => $a->is_correct,
                ]),
            ],
        ]);
    }
}
