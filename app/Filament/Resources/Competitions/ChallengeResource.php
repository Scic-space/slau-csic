<?php

namespace App\Filament\Resources\Competitions;

use App\Filament\Resources\Competitions\Pages\ManageChallenges;
use App\Models\Challenge;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Challenges';

    protected static string|UnitEnum|null $navigationGroup = 'Competitions';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('competition_id')
                    ->relationship('competition', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                Select::make('type')
                    ->options([
                        'flag' => 'Flag (exact match)',
                        'text' => 'Text (manual review)',
                    ])
                    ->required(),
                TextInput::make('answer')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Answer used for auto-verification (case-insensitive match).'),
                TextInput::make('points')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->sortable()
                    ->label('#'),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('competition.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('points')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions'),
            ])
            ->filters([
                SelectFilter::make('competition_id')
                    ->relationship('competition', 'name')
                    ->label('Competition'),
                SelectFilter::make('type')
                    ->options([
                        'flag' => 'Flag',
                        'text' => 'Text',
                    ]),
            ])
            ->defaultSort('sort_order')
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
            'index' => ManageChallenges::route('/'),
        ];
    }
}
