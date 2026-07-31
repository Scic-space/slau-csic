<?php

namespace App\Filament\Resources\Elections\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Action')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'vote_cast' => 'Vote cast',
                        'election_opened' => 'Election opened',
                        'election_closed' => 'Election closed',
                        'election_results_published' => 'Results published',
                        'election_auto_closed' => 'Auto-closed',
                        'nomination_submitted' => 'Nomination submitted',
                        'nomination_approved' => 'Nomination approved',
                        'nomination_rejected' => 'Nomination rejected',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                TextColumn::make('properties')
                    ->label('Details')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : '-')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
