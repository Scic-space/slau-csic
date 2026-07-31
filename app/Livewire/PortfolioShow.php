<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class PortfolioShow extends Component
{
    public ?User $student = null;

    public string $tab = 'projects';

    public function mount(string $slug): void
    {
        $this->student = User::with([
            'portfolioSkills',
            'portfolioCertifications',
            'portfolioExperiences',
            'portfolioEntries' => function ($query) {
                $query->where('is_published', true);
            },
        ])
            ->where('portfolio_slug', $slug)
            ->where('portfolio_is_public', true)
            ->firstOrFail();

        if (! auth()->check() && ! $this->student->portfolio_is_public) {
            abort(404);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.portfolio-show');
    }
}
