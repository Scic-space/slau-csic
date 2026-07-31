<?php

namespace App\Filament\Resources\Badges;

use App\Filament\Resources\Badges\Pages\ManageBadges;
use App\Models\Badge;
use App\Models\BadgeCriteriaType;
use App\Models\BadgeRarity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $slug = 'manage-badges';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from name'),
                Textarea::make('description'),
                TextInput::make('icon')
                    ->required(),
                Select::make('criteria_type')
                    ->options(collect(BadgeCriteriaType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->required(),
                TextInput::make('criteria_value')
                    ->numeric()
                    ->required(),
                TextInput::make('points_bonus')
                    ->numeric()
                    ->default(0),
                Select::make('rarity')
                    ->options(collect(BadgeRarity::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria_type')
                    ->badge(),
                TextColumn::make('criteria_value')
                    ->sortable(),
                TextColumn::make('points_bonus')
                    ->sortable(),
                TextColumn::make('rarity')->badge()
                    ->colors([
                        'gray' => 'common',
                        'blue' => 'rare',
                        'purple' => 'epic',
                        'amber' => 'legendary',
                    ]),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Awarded'),
            ])
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
            'index' => ManageBadges::route('/'),
        ];
    }
}
