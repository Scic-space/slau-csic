<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\ModuleProgress;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrainingShow extends Component
{
    use GuardsPendingMembers;

    public Training $training;

    public bool $enrolled = false;

    public array $moduleProgress = [];

    public function mount(Training $training): void
    {
        $this->training = $training->load(['modules', 'instructor', 'enrollments' => fn ($q) => $q->where('user_id', Auth::id())]);

        $this->enrolled = $this->training->enrollments->isNotEmpty();

        $this->loadModuleProgress();
    }

    public function enroll(): void
    {
        if ($this->enrolled) {
            return;
        }

        TrainingEnrollment::create([
            'training_id' => $this->training->id,
            'user_id' => Auth::id(),
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $this->enrolled = true;
    }

    public function completeModule(int $moduleId): void
    {
        if (! $this->enrolled) {
            return;
        }

        ModuleProgress::updateOrCreate(
            ['training_module_id' => $moduleId, 'user_id' => Auth::id()],
            ['completed' => true, 'completed_at' => now()]
        );

        $this->loadModuleProgress();
        $this->checkCompletion();
    }

    private function loadModuleProgress(): void
    {
        $progress = ModuleProgress::where('user_id', Auth::id())
            ->whereIn('training_module_id', $this->training->modules->pluck('id'))
            ->get()
            ->pluck('completed', 'training_module_id')
            ->toArray();

        $this->moduleProgress = $progress;
    }

    private function checkCompletion(): void
    {
        $totalModules = $this->training->modules->count();
        $completedCount = count(array_filter($this->moduleProgress));

        if ($totalModules > 0 && $completedCount >= $totalModules) {
            $enrollment = $this->training->enrollments->first();
            if ($enrollment && $enrollment->status !== 'completed') {
                $enrollment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'progress_percentage' => 100,
                ]);
            }
        } else {
            $enrollment = $this->training->enrollments->first();
            if ($enrollment) {
                $enrollment->update([
                    'status' => 'in_progress',
                    'progress_percentage' => $totalModules > 0 ? (int) round(($completedCount / $totalModules) * 100) : 0,
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.training-show', [
            'training' => $this->training,
            'enrolled' => $this->enrolled,
            'moduleProgress' => $this->moduleProgress,
        ]);
    }
}
