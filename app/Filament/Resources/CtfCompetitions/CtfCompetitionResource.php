<?php

namespace App\Filament\Resources\CtfCompetitions;

use App\Filament\Resources\CtfCompetitions\Pages\CreateCtfCompetition;
use App\Filament\Resources\CtfCompetitions\Pages\EditCtfCompetition;
use App\Filament\Resources\CtfCompetitions\Pages\ListCtfCompetitions;
use App\Filament\Resources\CtfCompetitions\RelationManagers\ChallengesRelationManager;
use App\Models\CtfCompetition;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CtfCompetitionResource extends Resource
{
    protected static ?string $model = CtfCompetition::class;

    protected static ?string $slug = 'manage-ctf-competitions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from title'),
                Select::make('event_id')
                    ->label('Linked Event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->nullable()
                    ->helperText('Link this CTF to an event for calendar/RSVP integration'),
                Textarea::make('description'),
                DateTimePicker::make('start_date'),
                DateTimePicker::make('end_date')
                    ->after('start_date'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required(),
                Toggle::make('is_public')
                    ->default(true),
                TextInput::make('max_score')
                    ->numeric()
                    ->minValue(0),
                Toggle::make('allow_teams')
                    ->label('Allow Teams')
                    ->default(false)
                    ->live(),
                TextInput::make('max_team_size')
                    ->numeric()
                    ->default(5)
                    ->minValue(2)
                    ->maxValue(20)
                    ->visible(fn ($get) => $get('allow_teams')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'gray' => 'archived',
                    ]),
                IconColumn::make('is_public')
                    ->boolean(),
                TextColumn::make('event.title')
                    ->label('Event')
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('allow_teams')
                    ->boolean()
                    ->label('Teams'),
                TextColumn::make('challenges_count')
                    ->counts('challenges')
                    ->label('Challenges'),
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->dateTime()
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
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (CtfCompetition $record): string => route('ctf.competition', ['competition' => $record, 'preview' => 1]))
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                Action::make('clone')
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalHeading('Clone Competition')
                    ->modalDescription('This will create a duplicate of this competition including all challenges and their hints.')
                    ->action(function (CtfCompetition $record) {
                        DB::transaction(function () use ($record) {
                            $clone = $record->replicate(['slug']);
                            $clone->title = $record->title.' (Clone)';
                            $clone->slug = $record->slug.'-clone-'.now()->timestamp;
                            $clone->status = 'draft';
                            $clone->save();

                            foreach ($record->challenges as $challenge) {
                                $challengeClone = $challenge->replicate(['slug']);
                                $challengeClone->ctf_competition_id = $clone->id;
                                $challengeClone->slug = $challenge->slug.'-clone-'.now()->timestamp;
                                $challengeClone->save();

                                foreach ($challenge->hints as $hint) {
                                    $hintClone = $hint->replicate();
                                    $hintClone->ctf_challenge_id = $challengeClone->id;
                                    $hintClone->save();
                                }

                                foreach ($challenge->files as $file) {
                                    $fileClone = $file->replicate();
                                    $fileClone->ctf_challenge_id = $challengeClone->id;
                                    $fileClone->save();
                                }
                            }
                        });

                        Notification::make()
                            ->title('Competition cloned successfully')
                            ->success()
                            ->send();
                    }),
            ], position: RecordActionsPosition::BeforeCells);
    }

    public static function getRelations(): array
    {
        return [
            ChallengesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCtfCompetitions::route('/'),
            'create' => CreateCtfCompetition::route('/create'),
            'edit' => EditCtfCompetition::route('/{record}/edit'),
        ];
    }
}
