<?php

namespace App\Filament\Resources\ExamAttempts\Tables;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(ExamAttempt::completed()->with(['user', 'exam']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable(),

                TextColumn::make('total_score')
                    ->label('Score'),

                TextColumn::make('passed')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Passed' : 'Failed'),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->options(fn (): array => Exam::orderBy('title')->pluck('title', 'id')->toArray()),
            ]);
    }
}
