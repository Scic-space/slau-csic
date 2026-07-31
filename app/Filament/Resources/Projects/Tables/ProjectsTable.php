<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'research' => 'info',
                        'development' => 'success',
                        'ctf' => 'danger',
                        'competition' => 'warning',
                        'community' => 'primary',
                        'security_audit' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'proposed' => 'info',
                        'on_hold' => 'warning',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable(),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('repository_url')
                    ->label('Repo')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => ! empty($record->repository_url)),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'research' => 'Research',
                        'development' => 'Development',
                        'ctf' => 'CTF',
                        'competition' => 'Competition',
                        'community' => 'Community',
                        'security_audit' => 'Security Audit',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'proposed' => 'Proposed',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
