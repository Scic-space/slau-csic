<?php

namespace App\Filament\Resources\ExamAttempts\RelationManagers;

use App\Models\ExamAnswer;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'Answers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('marks_awarded')
                    ->label('Marks Awarded')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Toggle::make('is_correct')
                    ->label('Marked Correct'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('examQuestion.question.question_text')
                    ->label('Question')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('examQuestion.question.type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),

                TextColumn::make('response')
                    ->label('Answer')
                    ->getStateUsing(fn (ExamAnswer $record): string => $record->selectedOption?->option_text
                        ?? $record->answer_text
                        ?? '—')
                    ->wrap(),

                TextColumn::make('is_correct')
                    ->label('Correct')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        true => 'success',
                        false => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        true => 'Correct',
                        false => 'Incorrect',
                        default => 'Ungraded',
                    }),

                TextColumn::make('marks_awarded')
                    ->label('Awarded'),

                TextColumn::make('examQuestion.effective_marks')
                    ->label('Max Marks'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
