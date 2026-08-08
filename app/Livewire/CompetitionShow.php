<?php

namespace App\Livewire;

use App\Models\Competition;
use App\Models\CompetitionParticipants;
use Livewire\Component;

class CompetitionShow extends Component
{
    public Competition $competition;

    public ?string $teamName = null;

    public ?string $role = null;

    public bool $showJoinForm = false;

    public bool $showLeaveConfirm = false;

    protected function rules(): array
    {
        $rules = [
            'role' => 'nullable|string|in:leader,member',
        ];

        if ($this->competition->is_team_based) {
            $rules['teamName'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function mount(Competition $competition): void
    {
        $this->competition = $competition->load(['members', 'challenges']);
    }

    public function title(): string
    {
        return $this->competition->name;
    }

    public function join(): void
    {
        if (auth()->user()?->isPendingApproval()) {
            $this->redirectRoute('dashboard');

            return;
        }

        $this->authorize('join', $this->competition);

        $this->validate();

        CompetitionParticipants::create([
            'competition_id' => $this->competition->id,
            'user_id' => auth()->id(),
            'team_name' => $this->teamName,
            'role' => $this->role ?? 'member',
        ]);

        $this->competition->load(['members']);
        $this->showJoinForm = false;
        $this->teamName = null;
        $this->role = null;

        $this->dispatch('toast-show', message: 'You have joined the competition!', type: 'success');
    }

    public function confirmLeave(): void
    {
        $this->showLeaveConfirm = true;
    }

    public function cancelLeave(): void
    {
        $this->showLeaveConfirm = false;
    }

    public function leave(): void
    {
        $this->authorize('leave', $this->competition);

        $this->competition->participants()
            ->where('user_id', auth()->id())
            ->delete();

        $this->competition->load(['members']);
        $this->showLeaveConfirm = false;

        $this->dispatch('toast-show', message: 'You have left the competition.', type: 'info');
    }

    public function toggleJoinForm(): void
    {
        $this->showJoinForm = ! $this->showJoinForm;
    }

    public function render()
    {
        $user = auth()->user();
        $userParticipation = null;

        if ($user) {
            $userParticipation = $this->competition->participants()
                ->where('user_id', $user->id)
                ->first();
        }

        return view('livewire.competition-show', [
            'competition' => [
                'id' => $this->competition->id,
                'name' => $this->competition->name,
                'description' => $this->competition->description,
                'type' => $this->competition->type,
                'start_date' => $this->competition->start_date?->toIso8601String(),
                'end_date' => $this->competition->end_date?->toIso8601String(),
                'location' => $this->competition->location,
                'is_team_based' => $this->competition->is_team_based,
                'max_team_size' => $this->competition->max_team_size,
                'website_url' => $this->competition->website_url,
                'participation_status' => $this->competition->participation_status,
                'club_ranking' => $this->competition->club_ranking,
                'achievements' => $this->competition->achievements,
                'status' => $this->competition->statusLabel(),
                'status_color' => $this->competition->statusColor(),
                'user_participation' => $userParticipation,
                'participants' => $this->competition->members->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'team_name' => $m->pivot->team_name,
                    'role' => $m->pivot->role,
                ]),
            ],
        ]);
    }
}
