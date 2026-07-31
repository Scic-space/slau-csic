<?php

namespace App\Exports;

use App\Models\Election;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ElectionParticipationExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(public Election $election) {}

    public function array(): array
    {
        $voterIds = $this->election->votes()->pluck('user_id');
        $eligibleIds = User::activeMembers()->pluck('id');

        $voted = User::whereIn('id', $voterIds)
            ->orderBy('name')
            ->get(['name', 'email'])
            ->map(fn ($u) => [$u->name, $u->email, 'Yes', $this->election->votes()
                ->where('user_id', $u->id)->first()?->created_at?->format('M j, Y g:i A') ?? 'N/A',
            ]);

        $notVoted = User::whereIn('id', $eligibleIds)
            ->whereNotIn('id', $voterIds)
            ->orderBy('name')
            ->get(['name', 'email'])
            ->map(fn ($u) => [$u->name, $u->email, 'No', 'N/A']);

        return $voted->concat($notVoted)->toArray();
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Voted', 'Voted At'];
    }

    public function title(): string
    {
        return Str::limit($this->election->title, 30).' - Voters';
    }
}
