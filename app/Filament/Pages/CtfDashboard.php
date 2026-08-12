<?php

namespace App\Filament\Pages;

use App\Models\CtfCompetition;
use App\Models\CtfSubmission;
use App\Models\CtfTeam;
use App\Models\CtfWriteup;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CtfDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected string $view = 'filament.pages.ctf-dashboard';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false;
    }

    public function getStats(): array
    {
        $activeCompetitions = CtfCompetition::published()
            ->currentlyActive()
            ->count();

        $pendingWriteups = CtfWriteup::where('status', 'pending')->count();

        $totalSolves = CtfSubmission::where('is_correct', true)->count();

        $totalParticipants = CtfSubmission::where('is_correct', true)
            ->distinct('user_id')
            ->count('user_id');

        $totalTeams = CtfTeam::count();

        return [
            'active_competitions' => $activeCompetitions,
            'pending_writeups' => $pendingWriteups,
            'total_solves' => $totalSolves,
            'total_participants' => $totalParticipants,
            'total_teams' => $totalTeams,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CtfCompetition::query()->withCount('challenges'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'gray' => 'archived',
                    ]),
                TextColumn::make('challenges_count')
                    ->label('Challenges'),
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
