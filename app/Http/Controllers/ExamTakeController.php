<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\ExamAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExamTakeController extends Controller
{
    public function saveAnswer(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->can('take', $exam), 403);

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

        abort_unless($user->can('take', $exam), 403);

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
