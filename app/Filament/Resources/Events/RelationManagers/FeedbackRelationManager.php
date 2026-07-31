<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackRelationManager extends RelationManager
{
    protected static string $relationship = 'feedback';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('rating'),
                TextColumn::make('content_quality')
                    ->label('Content'),
                TextColumn::make('instructor_rating')
                    ->label('Instructor'),
                TextColumn::make('feedback_text')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
