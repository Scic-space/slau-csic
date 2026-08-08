<?php

namespace App\Livewire;

use App\Models\PortfolioCertification;
use App\Models\PortfolioExperience;
use App\Models\PortfolioSkill;
use App\Models\StudentPortfolio;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TeacherPortfolios extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingPortfolioId = null;

    public string $search = '';

    public string $categoryFilter = '';

    public string $statusFilter = '';

    public string $activeSection = 'portfolios';

    // Portfolio form
    public string $formTitle = '';

    public string $formDescription = '';

    public string $formCategory = 'project';

    public string $formExternalLink = '';

    public string $formStudentId = '';

    public bool $formIsPublished = false;

    public $formFile = null;

    public string $formRepoUrl = '';

    public string $formLiveUrl = '';

    public string $formTechStack = '';

    // Skill form
    public bool $showSkillForm = false;

    public string $skillName = '';

    public string $skillCategory = 'general';

    public int $skillProficiency = 3;

    public string $skillStudentId = '';

    // Certification form
    public bool $showCertForm = false;

    public string $certName = '';

    public string $certIssuer = '';

    public string $certDateEarned = '';

    public string $certExpiryDate = '';

    public string $certCredentialUrl = '';

    public string $certCredentialId = '';

    public string $certStudentId = '';

    // Experience form
    public bool $showExpForm = false;

    public string $expTitle = '';

    public string $expOrganization = '';

    public string $expDescription = '';

    public string $expStartDate = '';

    public string $expEndDate = '';

    public bool $expIsCurrent = false;

    public string $expType = 'experience';

    public string $expStudentId = '';

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
        $this->showForm = false;
        $this->showSkillForm = false;
        $this->showCertForm = false;
        $this->showExpForm = false;
    }

    // --- Portfolio CRUD ---

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingPortfolioId = null;
    }

    public function openEditForm(int $id): void
    {
        $portfolio = StudentPortfolio::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $portfolio->created_by !== auth()->id()) {
            abort(403);
        }
        $this->editingPortfolioId = $portfolio->id;
        $this->formTitle = $portfolio->title;
        $this->formDescription = $portfolio->description ?? '';
        $this->formCategory = $portfolio->category;
        $this->formExternalLink = $portfolio->external_link ?? '';
        $this->formStudentId = (string) $portfolio->student_id;
        $this->formIsPublished = $portfolio->is_published;
        $this->formFile = null;
        $this->formRepoUrl = $portfolio->repo_url ?? '';
        $this->formLiveUrl = $portfolio->live_url ?? '';
        $this->formTechStack = is_array($portfolio->tech_stack) ? implode(', ', $portfolio->tech_stack) : '';
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function savePortfolio(): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formDescription' => ['nullable', 'string'],
            'formCategory' => ['required', 'string'],
            'formExternalLink' => ['nullable', 'url'],
            'formRepoUrl' => ['nullable', 'url'],
            'formLiveUrl' => ['nullable', 'url'],
            'formStudentId' => ['required', 'exists:users,id'],
            'formIsPublished' => ['boolean'],
            'formFile' => ['nullable', 'file', 'max:10240'],
        ]);

        $techStack = array_filter(array_map('trim', explode(',', $this->formTechStack)));

        $data = [
            'title' => $this->formTitle,
            'description' => $this->formDescription,
            'category' => $this->formCategory,
            'external_link' => $this->formExternalLink,
            'repo_url' => $this->formRepoUrl,
            'live_url' => $this->formLiveUrl,
            'tech_stack' => $techStack ?: null,
            'student_id' => $this->formStudentId,
            'is_published' => $this->formIsPublished,
            'created_by' => auth()->id(),
        ];

        if ($this->formFile) {
            $data['file_path'] = $this->formFile->store('portfolios', 'public');
        }

        if ($this->editingPortfolioId) {
            $portfolio = StudentPortfolio::findOrFail($this->editingPortfolioId);
            if ($portfolio->file_path && $this->formFile) {
                Storage::disk('public')->delete($portfolio->file_path);
            }
            $portfolio->update($data);
        } else {
            StudentPortfolio::create($data);
        }

        $this->closeForm();
    }

    public function deletePortfolio(int $id): void
    {
        $portfolio = StudentPortfolio::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $portfolio->created_by !== auth()->id()) {
            abort(403);
        }
        if ($portfolio->file_path && Storage::disk('public')->exists($portfolio->file_path)) {
            Storage::disk('public')->delete($portfolio->file_path);
        }
        $portfolio->delete();
    }

    public function togglePublish(int $id): void
    {
        $portfolio = StudentPortfolio::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $portfolio->created_by !== auth()->id()) {
            abort(403);
        }
        $portfolio->update(['is_published' => ! $portfolio->is_published]);
    }

    // --- Skill CRUD ---

    public function openSkillForm(): void
    {
        $this->resetSkillForm();
        $this->showSkillForm = true;
    }

    private function defaultStudentId(): string
    {
        return $this->canManage() ? '' : (string) auth()->id();
    }

    private function canManage(): bool
    {
        return auth()->user()->hasPermissionTo('portfolio.manage');
    }

    public function saveSkill(): void
    {
        $this->validate([
            'skillName' => ['required', 'string', 'max:255'],
            'skillCategory' => ['required', 'string'],
            'skillProficiency' => ['required', 'integer', 'min:1', 'max:5'],
            'skillStudentId' => ['required', 'exists:users,id'],
        ]);

        PortfolioSkill::create([
            'user_id' => $this->skillStudentId,
            'name' => $this->skillName,
            'category' => $this->skillCategory,
            'proficiency' => $this->skillProficiency,
        ]);

        $this->resetSkillForm();
    }

    public function deleteSkill(int $id): void
    {
        $skill = PortfolioSkill::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $skill->user_id !== auth()->id()) {
            abort(403);
        }
        $skill->delete();
    }

    private function resetSkillForm(): void
    {
        $this->showSkillForm = false;
        $this->skillName = '';
        $this->skillCategory = 'general';
        $this->skillProficiency = 3;
        $this->skillStudentId = $this->defaultStudentId();
    }

    // --- Certification CRUD ---

    public function openCertForm(): void
    {
        $this->resetCertForm();
        $this->showCertForm = true;
    }

    public function saveCert(): void
    {
        $this->validate([
            'certName' => ['required', 'string', 'max:255'],
            'certIssuer' => ['required', 'string', 'max:255'],
            'certDateEarned' => ['nullable', 'date'],
            'certExpiryDate' => ['nullable', 'date', 'after_or_equal:certDateEarned'],
            'certCredentialUrl' => ['nullable', 'url'],
            'certCredentialId' => ['nullable', 'string', 'max:255'],
            'certStudentId' => ['required', 'exists:users,id'],
        ]);

        PortfolioCertification::create([
            'user_id' => $this->certStudentId,
            'name' => $this->certName,
            'issuer' => $this->certIssuer,
            'date_earned' => $this->certDateEarned ?: null,
            'expiry_date' => $this->certExpiryDate ?: null,
            'credential_url' => $this->certCredentialUrl ?: null,
            'credential_id' => $this->certCredentialId ?: null,
        ]);

        $this->resetCertForm();
    }

    public function deleteCert(int $id): void
    {
        $cert = PortfolioCertification::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $cert->user_id !== auth()->id()) {
            abort(403);
        }
        $cert->delete();
    }

    private function resetCertForm(): void
    {
        $this->showCertForm = false;
        $this->certName = '';
        $this->certIssuer = '';
        $this->certDateEarned = '';
        $this->certExpiryDate = '';
        $this->certCredentialUrl = '';
        $this->certCredentialId = '';
        $this->certStudentId = $this->defaultStudentId();
    }

    // --- Experience CRUD ---

    public function openExpForm(): void
    {
        $this->resetExpForm();
        $this->showExpForm = true;
    }

    public function saveExp(): void
    {
        $this->validate([
            'expTitle' => ['required', 'string', 'max:255'],
            'expOrganization' => ['required', 'string', 'max:255'],
            'expDescription' => ['nullable', 'string'],
            'expStartDate' => ['required', 'date'],
            'expEndDate' => ['nullable', 'date', 'after_or_equal:expStartDate'],
            'expIsCurrent' => ['boolean'],
            'expType' => ['required', 'string'],
            'expStudentId' => ['required', 'exists:users,id'],
        ]);

        PortfolioExperience::create([
            'user_id' => $this->expStudentId,
            'title' => $this->expTitle,
            'organization' => $this->expOrganization,
            'description' => $this->expDescription ?: null,
            'start_date' => $this->expStartDate,
            'end_date' => $this->expEndDate ?: null,
            'is_current' => $this->expIsCurrent,
            'type' => $this->expType,
        ]);

        $this->resetExpForm();
    }

    public function deleteExp(int $id): void
    {
        $exp = PortfolioExperience::findOrFail($id);
        if (! auth()->user()->hasPermissionTo('portfolio.manage') && $exp->user_id !== auth()->id()) {
            abort(403);
        }
        $exp->delete();
    }

    private function resetExpForm(): void
    {
        $this->showExpForm = false;
        $this->expTitle = '';
        $this->expOrganization = '';
        $this->expDescription = '';
        $this->expStartDate = '';
        $this->expEndDate = '';
        $this->expIsCurrent = false;
        $this->expType = 'experience';
        $this->expStudentId = $this->defaultStudentId();
    }

    // --- Shared ---

    private function resetForm(): void
    {
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formCategory = 'project';
        $this->formExternalLink = '';
        $this->formStudentId = $this->defaultStudentId();
        $this->formIsPublished = false;
        $this->formFile = null;
        $this->formRepoUrl = '';
        $this->formLiveUrl = '';
        $this->formTechStack = '';
        $this->editingPortfolioId = null;
    }

    public function render()
    {
        $canManage = $this->canManage();

        $allQuery = StudentPortfolio::query();
        if (! $canManage) {
            $allQuery->where('student_id', auth()->id());
        }

        $query = (clone $allQuery)->with(['student', 'creator']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($this->categoryFilter !== '') {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        }

        $portfolios = $query->orderBy('created_at', 'desc')->get();

        $total = (clone $allQuery)->count();
        $published = (clone $allQuery)->where('is_published', true)->count();
        $drafts = $total - $published;

        $students = User::where('membership_status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $allSkills = PortfolioSkill::with('user')->orderBy('user_id')->get();
        $allCerts = PortfolioCertification::with('user')->orderByDesc('date_earned')->get();
        $allExps = PortfolioExperience::with('user')->orderByDesc('start_date')->get();

        return view('livewire.teacher-portfolios', [
            'portfolios' => $portfolios,
            'students' => $students,
            'categories' => StudentPortfolio::categories(),
            'canManage' => $canManage,
            'total' => $total,
            'published' => $published,
            'drafts' => $drafts,
            'allSkills' => $allSkills,
            'allCerts' => $allCerts,
            'allExps' => $allExps,
            'skillCategories' => PortfolioSkill::categories(),
            'expTypes' => PortfolioExperience::types(),
        ]);
    }
}
