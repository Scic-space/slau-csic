<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\ExamAttempt;
use App\Models\TrainingEnrollment;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Grades')]
class MyGrades extends Component
{
    use GuardsPendingMembers;

    public function render()
    {
        $user = auth()->user();

        $trainingGrades = TrainingEnrollment::where('user_id', $user->id)
            ->with('training')
            ->get()
            ->map(fn ($enrollment) => [
                'type' => 'training',
                'name' => $enrollment->training->title ?? 'Unknown Training',
                'status' => $enrollment->status,
                'progress' => $enrollment->progress_percentage,
                'score' => $enrollment->score,
                'rating' => $enrollment->rating,
                'feedback' => $enrollment->feedback,
                'completed_at' => $enrollment->completed_at,
                'enrolled_at' => $enrollment->enrolled_at,
            ]);

        $examGrades = ExamAttempt::where('user_id', $user->id)
            ->with('exam')
            ->whereNotNull('total_score')
            ->get()
            ->map(fn ($attempt) => [
                'type' => 'exam',
                'name' => $attempt->exam->title ?? 'Unknown Exam',
                'status' => $attempt->passed ? 'passed' : 'failed',
                'progress' => null,
                'score' => $attempt->total_score,
                'rating' => null,
                'feedback' => null,
                'completed_at' => $attempt->submitted_at,
                'enrolled_at' => $attempt->created_at,
            ]);

        $allGrades = collect()->merge($trainingGrades->values())->merge($examGrades->values())->sortByDesc('completed_at')->values();

        $stats = [
            'trainings_enrolled' => $trainingGrades->count(),
            'trainings_completed' => $trainingGrades->where('status', 'completed')->count(),
            'exams_taken' => $examGrades->count(),
            'exams_passed' => $examGrades->where('status', 'passed')->count(),
            'average_score' => $allGrades->whereNotNull('score')->avg('score'),
        ];

        return view('livewire.my-grades', [
            'grades' => $allGrades,
            'stats' => $stats,
        ]);
    }
}
