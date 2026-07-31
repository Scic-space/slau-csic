<?php

namespace App\Filament\Resources\Elections\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VotesRelationManager extends RelationManager
{
    protected static string $relationship = 'votes';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_code')
                    ->label('Receipt')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('candidate.name')
                    ->label('Candidate')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Voted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction(null);
    }
}
