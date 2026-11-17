<?php

namespace App\Filament\Resources\Exams\Tables;

use App\Models\Exam;
use App\Services\ExamService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Exam::with(['creator'])->withCount('questions'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => "{$state} min")
                    ->sortable(),

                TextColumn::make('passing_score')
                    ->label('Pass Score')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                Action::make('toggle_status')
                    ->label('Toggle Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Exam $record): void {
                        $newStatus = app(ExamService::class)->toggleStatus($record);

                        $label = ucfirst($newStatus);

                        Notification::make()
                            ->title("Exam is now {$label}")
                            ->body($newStatus === 'published' ? 'Members have been notified.' : null)
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
