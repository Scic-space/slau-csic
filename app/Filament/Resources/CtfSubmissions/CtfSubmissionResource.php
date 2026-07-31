<?php

namespace App\Filament\Resources\CtfSubmissions;

use App\Filament\Resources\CtfSubmissions\Pages\ManageCtfSubmissions;
use App\Models\CtfSubmission;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CtfSubmissionResource extends Resource
{
    protected static ?string $model = CtfSubmission::class;

    protected static ?string $slug = 'manage-ctf-submissions';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ctf_challenge_id')
                    ->relationship('challenge', 'title'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('ctf_team_id')
                    ->relationship('team', 'name')
                    ->nullable(),
                TextInput::make('submitted_flag'),
                Select::make('is_correct')
                    ->options([
                        '1' => 'Correct',
                        '0' => 'Incorrect',
                    ]),
                TextInput::make('points_awarded')
                    ->numeric(),
                TextInput::make('attempt_number')
                    ->numeric(),
                TextInput::make('ip_address'),
                DateTimePicker::make('submitted_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('challenge.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge.competition.title')
                    ->label('Competition')
                    ->sortable(),
                TextColumn::make('is_correct')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Correct' : 'Incorrect'),
                TextColumn::make('points_awarded')
                    ->sortable(),
                TextColumn::make('attempt_number')
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_correct')
                    ->label('Result')
                    ->options([
                        '1' => 'Correct',
                        '0' => 'Incorrect',
                    ]),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCtfSubmissions::route('/'),
        ];
    }
}
