<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Meeting;
use App\Models\TeachingMaterial;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Teaching Dashboard')]
class InstructorDashboard extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('teacher.dashboard.view'), 403);
    }

    public function render()
    {
        $user = auth()->user();

        $isOverseer = $user->can('access_admin_panel');

        $myTrainingIds = Training::query()
            ->when(! $isOverseer, fn ($query) => $query->where('instructor_id', $user->id))
            ->pluck('id');

        $myExamIds = Exam::query()
            ->when(! $isOverseer, fn ($query) => $query->where('user_id', $user->id))
            ->pluck('id');

        $teachingSessions = fn () => Meeting::teachingSessions()
            ->when(! $isOverseer, fn ($query) => $query->where('created_by', $user->id));

        $materials = fn () => TeachingMaterial::query()
            ->when(! $isOverseer, fn ($query) => $query->where('uploaded_by', $user->id));

        $examIndexUrl = route('exams.index');
        $examsUrl = $isOverseer ? url('/admin/exams') : $examIndexUrl;
        $examAttemptsUrl = $isOverseer ? url('/admin/exam-attempts') : $examIndexUrl;

        $submittedAttempts = ExamAttempt::whereIn('exam_id', $myExamIds)
            ->whereNotNull('submitted_at');

        $pendingGrading = (clone $submittedAttempts)
            ->whereHas('answers', fn ($q) => $q
                ->whereNull('is_correct')
                ->where('marks_awarded', 0)
                ->whereHas('examQuestion.question', fn ($qq) => $qq->where('type', 'short_answer')))
            ->count();

        $stats = [
            'trainings' => $myTrainingIds->count(),
            'active_enrollments' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)
                ->active()
                ->count(),
            'completed_students' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)
                ->completed()
                ->count(),
            'teaching_sessions' => $teachingSessions()->count(),
            'materials' => $materials()->count(),
            'exams' => $myExamIds->count(),
            'exam_attempts' => (clone $submittedAttempts)->count(),
            'pending_exam_grading' => $pendingGrading,
        ];

        $teachingCards = [
            [
                'label' => 'Total Trainings',
                'icon' => 'school',
                'count' => $stats['trainings'],
                'tone' => 'indigo',
                'url' => route('instructor.trainings'),
            ],
            [
                'label' => 'Upcoming Sessions',
                'icon' => 'event_upcoming',
                'count' => $teachingSessions()->upcoming()->count(),
                'tone' => 'teal',
                'url' => route('instructor.sessions'),
            ],
            [
                'label' => 'Completed Sessions',
                'icon' => 'task_alt',
                'count' => Meeting::completedTeachingSessions()
                    ->when(! $isOverseer, fn ($query) => $query->where('created_by', $user->id))
                    ->count(),
                'tone' => 'green',
                'url' => route('instructor.sessions'),
            ],
            [
                'label' => 'Course Materials',
                'icon' => 'menu_book',
                'count' => $stats['materials'],
                'tone' => 'amber',
                'url' => route('instructor.materials'),
            ],
            [
                'label' => 'Students',
                'icon' => 'groups',
                'count' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)->distinct('user_id')->count('user_id'),
                'tone' => 'blue',
                'url' => route('members.index'),
            ],
            [
                'label' => 'Pending Grades',
                'icon' => 'grading',
                'count' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)
                    ->completed()
                    ->whereNull('score')
                    ->count(),
                'tone' => 'orange',
                'url' => route('instructor.trainings'),
            ],
            [
                'label' => 'Total Exams',
                'icon' => 'quiz',
                'count' => $stats['exams'],
                'tone' => 'blue',
                'url' => $examsUrl,
            ],
            [
                'label' => 'Exam Attempts',
                'icon' => 'assignment',
                'count' => $stats['exam_attempts'],
                'tone' => 'teal',
                'url' => $examAttemptsUrl,
            ],
            [
                'label' => 'Pending Grading',
                'icon' => 'pending_actions',
                'count' => $stats['pending_exam_grading'],
                'tone' => 'orange',
                'url' => $examAttemptsUrl,
            ],
        ];

        $recentEnrollments = TrainingEnrollment::whereIn('training_id', $myTrainingIds)
            ->with(['user', 'training'])
            ->latest('enrolled_at')
            ->limit(5)
            ->get();

        $recentSessions = $teachingSessions()
            ->latest('scheduled_at')
            ->limit(5)
            ->get();

        $recentExamAttempts = (clone $submittedAttempts)
            ->with(['user', 'exam'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $recentMaterials = $materials()
            ->with('training')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.instructor-dashboard', [
            'stats' => $stats,
            'teachingCards' => $teachingCards,
            'recentEnrollments' => $recentEnrollments,
            'recentSessions' => $recentSessions,
            'recentExamAttempts' => $recentExamAttempts,
            'recentMaterials' => $recentMaterials,
        ]);
    }
}
