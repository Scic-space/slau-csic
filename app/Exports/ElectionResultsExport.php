<?php

namespace App\Exports;

use App\Models\Election;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ElectionResultsExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public Election $election)
    {
        $this->election->loadMissing('candidates.votes');
    }

    public function array(): array
    {
        $totalVotes = $this->election->votes()->count();

        return $this->election->candidates
            ->sortByDesc(fn ($c) => $c->votes->count())
            ->values()
            ->map(fn ($c, $i) => [
                $i + 1,
                $c->name,
                $c->votes->count(),
                $totalVotes > 0 ? round(($c->votes->count() / $totalVotes) * 100, 1) : 0,
            ])
            ->toArray();
    }

    public function headings(): array
    {
        return ['Rank', 'Candidate', 'Votes', 'Percentage (%)'];
    }

    public function title(): string
    {
        return Str::limit($this->election->title, 30);
    }
}
