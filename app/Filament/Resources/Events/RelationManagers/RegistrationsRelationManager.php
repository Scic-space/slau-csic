<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\EventRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'waitlist' => 'Waitlist',
                        'attended' => 'Attended',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ]),
                Textarea::make('notes'),
                TextInput::make('custom_fields')
                    ->label('Custom Fields (JSON)'),
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
                    ->searchable(),
                TextColumn::make('status')->badge()
                    ->colors([
                        'success' => 'attended',
                        'primary' => 'registered',
                        'warning' => 'waitlist',
                        'danger' => 'cancelled',
                        'gray' => 'no_show',
                    ]),
                TextColumn::make('rsvp_status')->badge()
                    ->label('RSVP')
                    ->colors([
                        'success' => 'attending',
                        'danger' => 'not_attending',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-'),
                TextColumn::make('registered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('attended_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'waitlist' => 'Waitlist',
                        'attended' => 'Attended',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ]),
            ])
            ->defaultSort('registered_at', 'desc')
            ->recordActions([
                Action::make('mark_attended')
                    ->label('Mark Attended')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EventRegistration $record): bool => is_null($record->attended_at) && $record->status !== 'cancelled')
                    ->action(function (EventRegistration $record) {
                        $record->update([
                            'attended_at' => now(),
                            'status' => 'attended',
                        ]);
                    }),
                Action::make('cancel')
                    ->label('Cancel Registration')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EventRegistration $record): bool => $record->status === 'registered' || $record->status === 'waitlist')
                    ->action(function (EventRegistration $record) {
                        $record->update(['status' => 'cancelled']);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
