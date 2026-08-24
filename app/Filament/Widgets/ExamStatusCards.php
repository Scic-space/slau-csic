<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Support\MaterialIconStat as Stat;
use App\Models\Exam;
use Filament\Widgets\StatsOverviewWidget;

class ExamStatusCards extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            $this->status('All', Exam::query()->count(), 'quiz', 'all', 'info'),
            $this->status('Published', Exam::query()->where('status', 'published')->count(), 'publish', 'published', 'success'),
            $this->status('Draft', Exam::query()->where('status', 'draft')->count(), 'edit_note', 'draft', 'warning'),
            $this->status('Archived', Exam::query()->where('status', 'archived')->count(), 'inventory_2', 'archived', 'gray'),
        ];
    }

    private function status(string $label, int $count, string $icon, string $tab, string $color): Stat
    {
        $isActive = request()->string('tab', 'all')->toString() === $tab;

        return Stat::make($label, number_format($count))
            ->icon($icon)
            ->description('View exams')
            ->descriptionIcon('arrow_forward')
            ->color($color)
            ->url(ExamResource::getUrl('index', ['tab' => $tab]))
            ->extraAttributes([
                'class' => 'rounded-sm transition hover:-translate-y-0.5 hover:shadow-md'.($isActive ? ' ring-2 ring-primary-500/30' : ''),
                'aria-current' => $isActive ? 'page' : null,
            ]);
    }

    /** @return array<string, int> */
    protected function getColumns(): array
    {
        return ['default' => 1, 'md' => 2, 'lg' => 3];
    }
}
