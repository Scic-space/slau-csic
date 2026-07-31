<?php

namespace App\Filament\Resources\Competitions;

use App\Filament\Resources\Competitions\Pages\ManageCompetitions;
use App\Models\Competition;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CompetitionResource extends Resource
{
    protected static ?string $model = Competition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'Competitions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options([
                        'ctf' => 'CTF',
                        'hackathon' => 'Hackathon',
                        'coding' => 'Coding',
                        'cybersecurity' => 'Cybersecurity',
                    ])
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                TextInput::make('location')
                    ->maxLength(255),
                TextInput::make('website_url')
                    ->url()
                    ->maxLength(255),
                Toggle::make('is_team_based')
                    ->default(true)
                    ->live(),
                TextInput::make('max_team_size')
                    ->numeric()
                    ->minValue(2)
                    ->maxValue(50)
                    ->visible(fn (callable $get) => $get('is_team_based')),
                Select::make('participation_status')
                    ->options([
                        'registered' => 'Registered',
                        'participating' => 'Participating',
                        'completed' => 'Completed',
                    ])
                    ->default('registered'),
                TextInput::make('club_ranking')
                    ->numeric()
                    ->minValue(1)
                    ->label('Club Ranking'),
                Textarea::make('achievements')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'indigo' => 'ctf',
                        'warning' => 'hackathon',
                        'success' => 'coding',
                        'info' => 'cybersecurity',
                    ])
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->limit(20),
                IconColumn::make('is_team_based')
                    ->boolean()
                    ->label('Team'),
                TextColumn::make('participants_count')
                    ->counts('participants')
                    ->label('Participants')
                    ->sortable(),
                TextColumn::make('club_ranking')
                    ->label('Ranking')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'ctf' => 'CTF',
                        'hackathon' => 'Hackathon',
                        'coding' => 'Coding',
                        'cybersecurity' => 'Cybersecurity',
                    ]),
                SelectFilter::make('participation_status')
                    ->options([
                        'registered' => 'Registered',
                        'participating' => 'Participating',
                        'completed' => 'Completed',
                    ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                EditAction::make(),
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
            'index' => ManageCompetitions::route('/'),
        ];
    }
}
