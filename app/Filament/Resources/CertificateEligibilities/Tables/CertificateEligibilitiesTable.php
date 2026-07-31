<?php

namespace App\Filament\Resources\CertificateEligibilities\Tables;

use App\Models\Exam;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CertificateEligibilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable(),

                TextColumn::make('eligible')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Eligible' : 'Revoked'),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Awarded')
                    ->dateTime('M j, Y'),
            ])
            ->filters([
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->options(fn (): array => Exam::whereHas('certificateEligibilities')->orderBy('title')->pluck('title', 'id')->toArray()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
