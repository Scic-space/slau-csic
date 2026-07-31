<?php

namespace App\Filament\Resources\Registrations;

use App\Filament\Resources\Registrations\Pages\EditEventRegistration;
use App\Filament\Resources\Registrations\Pages\ListEventRegistrations;
use App\Models\EventRegistration;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $slug = 'registrations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'waitlist' => 'Waitlisted',
                        'cancelled' => 'Cancelled',
                        'attended' => 'Attended',
                    ])
                    ->required(),
                Select::make('rsvp_status')
                    ->options([
                        'pending' => 'Pending',
                        'attending' => 'Attending',
                        'not_attending' => 'Not Attending',
                    ]),
                Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('custom_fields')
                    ->label('Custom Fields (JSON)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'primary',
                        'waitlist' => 'warning',
                        'cancelled' => 'danger',
                        'attended' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('rsvp_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'attending' => 'success',
                        'not_attending' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('registered_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('attended_at')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'waitlist' => 'Waitlisted',
                        'cancelled' => 'Cancelled',
                        'attended' => 'Attended',
                    ]),
                SelectFilter::make('rsvp_status')
                    ->options([
                        'pending' => 'Pending',
                        'attending' => 'Attending',
                        'not_attending' => 'Not Attending',
                    ]),
            ])
            ->defaultSort('registered_at', 'desc')
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventRegistrations::route('/'),
            'edit' => EditEventRegistration::route('/{record}/edit'),
        ];
    }
}
