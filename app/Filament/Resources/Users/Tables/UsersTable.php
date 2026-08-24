<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminActionStyle;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query()->with(['roles', 'memberProfile']))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('memberProfile.year_of_study')
                    ->label('Year')
                    ->formatStateUsing(fn ($state) => $state ? "Year {$state}" : '-'),

                TextColumn::make('memberProfile.faculty')
                    ->label('Faculty')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('memberProfile', fn (Builder $q): Builder => $q->where('faculty', 'like', "%{$search}%"))),

                TextColumn::make('memberProfile.phone')
                    ->label('Phone')
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('memberProfile', fn (Builder $q): Builder => $q->where('phone', 'like', "%{$search}%"))),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', ucfirst($state)))
                    ->badge()
                    ->color('primary')
                    ->separator(','),
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
                    ->label('Year of Study')
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value']),
                        fn (Builder $q): Builder => $q->whereHas('memberProfile', fn (Builder $p): Builder => $p->where('year_of_study', $data['value']))
                    )),

                SelectFilter::make('intake')
                    ->options([
                        'august' => 'August',
                        'january' => 'January',
                    ])
                    ->label('Intake'),

                SelectFilter::make('intake_year')
                    ->options(fn (): array => collect(range(now()->year - 5, now()->year))
                        ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
                        ->all())
                    ->label('Intake Year'),

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
                AdminActionStyle::apply(ViewAction::make(), 'View', 'visibility', 'info')
                    ->form(UserResource::getViewForm()),

                AdminActionStyle::apply(EditAction::make(), 'Edit', 'edit', 'teal'),

                AdminActionStyle::apply(DeleteAction::make(), 'Delete', 'delete', 'danger'),

                Action::make('approve')
                    ->label('Approve')
                    ->icon(self::materialIcon('check_circle'))
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Approve')
                    ->extraAttributes(self::accessibleActionAttributes('Approve'))
                    ->visible(fn (User $record): bool => $record->membership_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->approve(auth()->user());
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(self::materialIcon('cancel'))
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Reject')
                    ->extraAttributes(self::accessibleActionAttributes('Reject'))
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
                    ->icon(self::materialIcon('pause_circle'))
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Suspend')
                    ->extraAttributes(self::accessibleActionAttributes('Suspend'))
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
                    ->icon(self::materialIcon('restart_alt'))
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Reactivate')
                    ->extraAttributes(self::accessibleActionAttributes('Reactivate'))
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
                    ->icon(self::materialIcon('school'))
                    ->iconButton()
                    ->color('purple')
                    ->tooltip('Convert to Alumni')
                    ->extraAttributes(self::accessibleActionAttributes('Convert to Alumni'))
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

    private static function materialIcon(string $name): HtmlString
    {
        return new HtmlString('<span class="material-symbols-outlined" aria-hidden="true">'.e($name).'</span>');
    }

    /**
     * @return array{aria-label: string, title: string}
     */
    private static function accessibleActionAttributes(string $label): array
    {
        return [
            'aria-label' => $label,
            'title' => $label,
        ];
    }
}
