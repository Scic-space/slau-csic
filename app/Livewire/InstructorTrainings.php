<?php

namespace App\Livewire;

use App\Models\Training;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Trainings')]
class InstructorTrainings extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $trainings = Training::where('instructor_id', auth()->id())
            ->withCount(['modules', 'enrollments'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($this->status === 'draft', fn ($q) => $q->where('is_published', false))
            ->latest()
            ->paginate(12);

        return view('livewire.instructor-trainings', [
            'trainings' => $trainings,
            'categories' => [
                'ethical_hacking' => 'Ethical Hacking',
                'digital_forensics' => 'Digital Forensics',
                'network_security' => 'Network Security',
                'web_security' => 'Web Security',
                'mobile_security' => 'Mobile Security',
                'ctf' => 'CTF',
                'programming' => 'Programming',
                'other' => 'Other',
            ],
        ]);
    }
}
