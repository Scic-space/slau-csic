<?php

namespace App\Livewire;

use App\Models\TeachingMaterial;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Course Materials')]
class InstructorMaterials extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $formTitle = '';

    public string $formDescription = '';

    public string $formType = 'document';

    public string $formUrl = '';

    public ?int $formTrainingId = null;

    public $formFile = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $material = TeachingMaterial::where('uploaded_by', Auth::id())->findOrFail($id);

        $this->editingId = $material->id;
        $this->formTitle = $material->title;
        $this->formDescription = $material->description ?? '';
        $this->formType = $material->type;
        $this->formUrl = $material->url ?? '';
        $this->formTrainingId = $material->training_id;
        $this->formFile = null;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formDescription' => ['nullable', 'string', 'max:1000'],
            'formType' => ['required', 'in:video,document,slide,code,link,other'],
            'formUrl' => ['nullable', 'url'],
            'formTrainingId' => ['nullable', 'exists:trainings,id'],
            'formFile' => ['nullable', 'file', 'max:10240'],
        ]);

        $data = [
            'title' => $this->formTitle,
            'description' => $this->formDescription ?: null,
            'type' => $this->formType,
            'training_id' => $this->formTrainingId,
            'visibility' => 'all',
        ];

        if ($this->formType === 'link' && $this->formUrl) {
            $data['url'] = $this->formUrl;
        }

        if ($this->formFile) {
            $data['file_path'] = $this->formFile->store('teaching-materials', 'public');
        }

        if ($this->editingId) {
            $material = TeachingMaterial::where('uploaded_by', Auth::id())->findOrFail($this->editingId);

            if ($material->file_path && $this->formFile) {
                Storage::disk('public')->delete($material->file_path);
            }

            $material->update($data);
        } else {
            $data['uploaded_by'] = Auth::id();
            TeachingMaterial::create($data);
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        $material = TeachingMaterial::where('uploaded_by', Auth::id())->findOrFail($id);

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
    }

    private function resetForm(): void
    {
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formType = 'document';
        $this->formUrl = '';
        $this->formTrainingId = null;
        $this->formFile = null;
        $this->editingId = null;
    }

    public function render()
    {
        $materials = TeachingMaterial::where('uploaded_by', Auth::id())
            ->with('training')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest()
            ->paginate(12);

        $trainings = Training::where('instructor_id', Auth::id())
            ->orderBy('title')
            ->pluck('title', 'id');

        return view('livewire.instructor-materials', [
            'materials' => $materials,
            'trainings' => $trainings,
        ]);
    }
}
