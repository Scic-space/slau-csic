<?php

namespace App\Livewire;

use App\Models\Meeting;
use App\Models\TeachingMaterial;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Teaching Dashboard')]
class InstructorDashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $myTrainingIds = Training::where('instructor_id', $user->id)->pluck('id');

        $stats = [
            'trainings' => $myTrainingIds->count(),
            'active_enrollments' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)
                ->active()
                ->count(),
            'completed_students' => TrainingEnrollment::whereIn('training_id', $myTrainingIds)
                ->completed()
                ->count(),
            'teaching_sessions' => Meeting::teachingSessions()
                ->where('created_by', $user->id)
                ->count(),
            'materials' => TeachingMaterial::where('uploaded_by', $user->id)->count(),
        ];

        $recentEnrollments = TrainingEnrollment::whereIn('training_id', $myTrainingIds)
            ->with(['user', 'training'])
            ->latest('enrolled_at')
            ->limit(5)
            ->get();

        $recentSessions = Meeting::teachingSessions()
            ->where('created_by', $user->id)
            ->latest('scheduled_at')
            ->limit(5)
            ->get();

        $recentMaterials = TeachingMaterial::where('uploaded_by', $user->id)
            ->with('training')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.instructor-dashboard', [
            'stats' => $stats,
            'recentEnrollments' => $recentEnrollments,
            'recentSessions' => $recentSessions,
            'recentMaterials' => $recentMaterials,
        ]);
    }
}
