<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\ExamAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamTakeController extends Controller
{
    public function show(Request $request, Exam $exam): Response|RedirectResponse
    {
        $user = $request->user();
        $service = app(ExamAttemptService::class);
        $existingAttempt = $service->getUserAttempt($exam, $user);

        if ($existingAttempt && $existingAttempt->is_completed) {
            return redirect()->route('exams.result', $existingAttempt);
        }

        if (! $existingAttempt) {
            $attempt = $service->startAttempt($exam, $user);
        } else {
            $attempt = $existingAttempt;
        }

        $timeRemaining = $attempt->time_remaining_seconds ?? ($exam->duration_minutes * 60);

        $questions = $exam->questions()->with(['question.options'])->get();
        $questionsData = $questions->map(fn ($q) => [
            'id' => $q->id,
            'order' => $q->order,
            'custom_marks' => $q->custom_marks,
            'question' => [
                'id' => $q->question->id,
                'type' => $q->question->type,
                'question_text' => $q->question->question_text,
                'image' => $q->question->image ? \Illuminate\Support\Facades\Storage::url($q->question->image) : null,
                'code_block' => $q->question->code_block,
                'code_language' => $q->question->code_language,
                'marks' => $q->question->marks,
                'explanation' => $q->question->explanation,
                'options' => $q->question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'order' => $o->order,
                ]),
            ],
        ]);

        $answers = [];
        foreach ($attempt->answers as $answer) {
            if ($answer->answer_text) {
                $answers[$answer->exam_question_id]['text'] = $answer->answer_text;
            }
            if ($answer->selected_option_id) {
                $answers[$answer->exam_question_id]['option_id'] = $answer->selected_option_id;
            }
        }

        return Inertia::render('exams/Take', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score' => $exam->passing_score,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'time_remaining_seconds' => $timeRemaining,
            ],
            'questions' => $questionsData,
            'initialAnswers' => $answers,
        ]);
    }

    public function saveAnswer(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();
        $service = app(ExamAttemptService::class);
        $attempt = $service->getUserAttempt($exam, $user);

        abort_unless($attempt && ! $attempt->is_completed, 403);

        $request->validate([
            'question_id' => ['required', 'exists:exam_questions,id'],
            'answer_text' => ['nullable', 'string'],
            'selected_option_id' => ['nullable', 'exists:question_bank_options,id'],
        ]);

        $question = $exam->questions()->findOrFail($request->question_id);
        $data = array_filter([
            'answer_text' => $request->answer_text,
            'selected_option_id' => $request->selected_option_id,
        ]);

        if (! empty($data)) {
            try {
                $service->saveAnswer($attempt, $question, $data);
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('flash', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back();
    }

    public function submit(Request $request, Exam $exam, ExamAttemptService $service): RedirectResponse
    {
        $user = $request->user();
        $attempt = $service->getUserAttempt($exam, $user);

        abort_unless($attempt && ! $attempt->is_completed, 403);

        if ($service->isExpired($attempt)) {
            $service->submitAttempt($attempt);

            return redirect()->route('exams.result', $attempt);
        }

        $questions = $exam->questions;
        $answers = $attempt->answers->keyBy('exam_question_id');

        $unanswered = $questions->filter(fn ($q) => ! $answers->has($q->id))->count();
        if ($unanswered > 0) {
            return redirect()->back()->with('flash', ['error' => 'Please answer all questions before submitting.']);
        }

        $service->submitAttempt($attempt);

        return redirect()->route('exams.result', $attempt);
    }
}
