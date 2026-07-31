<?php

namespace App\Filament\Resources\Trainings\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $recordTitleAttribute = 'user.name';

    protected static ?string $title = 'Enrollments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('feedback')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable()
                    ->hidden(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'enrolled',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ]),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : '0%'),
                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : '-'),
                TextColumn::make('enrolled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->nullable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'enrolled' => 'Enrolled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->recordActions([
                Action::make('update_score')
                    ->label('Update Score')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form([
                        TextInput::make('score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'score' => $data['score'],
                        ]);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
