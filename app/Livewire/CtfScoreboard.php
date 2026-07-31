<?php

namespace App\Livewire;

use App\Models\CtfCompetition;
use App\Services\CtfScoreboardService;
use Livewire\Component;

class CtfScoreboard extends Component
{
    public int $competitionId;

    public string $viewMode = 'auto';

    public int $pollInterval = 15;

    public function mount(int $competitionId, string $viewMode = 'auto'): void
    {
        $this->competitionId = $competitionId;
        $this->viewMode = $viewMode;
    }

    public function competition(): CtfCompetition
    {
        return CtfCompetition::findOrFail($this->competitionId);
    }

    public function getScoreboardProperty(): \Illuminate\Support\Collection
    {
        $competition = $this->competition();
        $scoreboardService = app(CtfScoreboardService::class);

        $useTeams = $this->viewMode === 'team'
            || ($this->viewMode === 'auto' && $competition->allow_teams);

        return $useTeams
            ? $scoreboardService->getTeamScoreboard($competition, 100)
            : $scoreboardService->getScoreboard($competition, 100);
    }

    public function getUserRankProperty(): ?int
    {
        $competition = $this->competition();
        $scoreboardService = app(CtfScoreboardService::class);

        return auth()->check()
            ? $scoreboardService->getUserRank($competition, auth()->user())
            : null;
    }

    public function render()
    {
        return view('livewire.ctf-scoreboard', [
            'competition' => $this->competition(),
            'scoreboard' => $this->scoreboard,
            'userRank' => $this->userRank,
        ]);
    }
}
