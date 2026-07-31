<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Models\Training;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Training::with(['instructor'])->withCount('enrollments'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'ethical_hacking' => 'danger',
                        'digital_forensics' => 'warning',
                        'network_security' => 'info',
                        'web_security' => 'primary',
                        'mobile_security' => 'success',
                        'ctf' => 'gray',
                        'programming' => 'purple',
                        default => 'gray',
                    }),

                TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                    }),

                TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->sortable(),

                TextColumn::make('enrollments_count')
                    ->label('Enrolled')
                    ->counts('enrollments')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'ethical_hacking' => 'Ethical Hacking',
                        'digital_forensics' => 'Digital Forensics',
                        'network_security' => 'Network Security',
                        'web_security' => 'Web Security',
                        'mobile_security' => 'Mobile Security',
                        'ctf' => 'CTF',
                        'programming' => 'Programming',
                        'other' => 'Other',
                    ]),

                SelectFilter::make('difficulty')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),

                SelectFilter::make('is_published')
                    ->label('Status')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ]),
            ])
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
