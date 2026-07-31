<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query()->with(['roles']))
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->getStateUsing(fn (User $record): string => $record->avatar_url)
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied'),

                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('registration_number')
                    ->label('Reg. Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(fn ($record): string => $record->program),

                TextColumn::make('year_of_study')
                    ->label('Year')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? "Year {$state}" : '-'),

                TextColumn::make('membership_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'associate' => 'warning',
                        'alumni' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('membership_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        'inactive' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', ucfirst($state)))
                    ->badge()
                    ->color('primary')
                    ->separator(','),

                TextColumn::make('joined_at')
                    ->label('Joined')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->sortable()
                    ->formatStateUsing(fn (User $record): string => match (true) {
                        $record->approved_at !== null => $record->approved_at->format('M d, Y'),
                        $record->membership_status === 'pending' => 'Pending',
                        default => '—',
                    }),
            ])
            ->filters([
                SelectFilter::make('membership_type')
                    ->options([
                        'active' => 'Active Member',
                        'associate' => 'Associate Member',
                        'alumni' => 'Alumni',
                    ])
                    ->label('Membership Type'),

                SelectFilter::make('membership_status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'suspended' => 'Suspended',
                        'inactive' => 'Inactive',
                    ])
                    ->label('Membership Status'),

                SelectFilter::make('year_of_study')
                    ->options([
                        1 => 'Year 1',
                        2 => 'Year 2',
                        3 => 'Year 3',
                        4 => 'Year 4',
                        5 => 'Year 5',
                    ])
                    ->label('Year of Study'),

                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Roles')
                    ->searchable(),

                Filter::make('pending_approval')
                    ->label('Pending Approval')
                    ->query(fn (Builder $query): Builder => $query->where('membership_status', 'pending')),

                Filter::make('alumni')
                    ->label('Alumni Only')
                    ->query(fn (Builder $query): Builder => $query->where('membership_type', 'alumni')),

                Filter::make('approved')
                    ->label('Approved')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('approved_at')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->form(UserResource::getViewForm()),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->membership_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->approve(auth()->user());
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->membership_status === 'pending')
                    ->schema([
                        Textarea::make('rejection_notes')
                            ->label('Rejection Notes')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record) {
                        $record->reject(auth()->user(), $data['rejection_notes']);
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->membership_status === 'active')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required(),
                        DateTimePicker::make('suspended_until')
                            ->label('Suspend Until (optional)'),
                    ])
                    ->action(function (array $data, User $record) {
                        $record->suspend(
                            $data['reason'],
                            auth()->user(),
                            $data['suspended_until'] ? Carbon::parse($data['suspended_until']) : null,
                        );
                    }),

                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->membership_status === 'suspended')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update([
                            'membership_status' => 'active',
                            'suspension_reason' => null,
                            'suspended_until' => null,
                            'suspended_by' => null,
                        ]);
                    }),

                Action::make('convert_to_alumni')
                    ->label('Convert to Alumni')
                    ->icon('heroicon-o-academic-cap')
                    ->color('gray')
                    ->visible(fn (User $record): bool => $record->membership_status === 'active' && $record->membership_type !== 'alumni')
                    ->requiresConfirmation()
                    ->modalHeading('Convert to Alumni')
                    ->modalDescription('This will mark the member as alumni. This action can be reversed.')
                    ->action(function (User $record) {
                        $record->convertToAlumni();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (User $record) => $record->approve(auth()->user()));
                        }),
                    BulkAction::make('bulk_reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (User $record) => $record->reject(auth()->user()));
                        }),
                ]),
            ]);
    }
}
