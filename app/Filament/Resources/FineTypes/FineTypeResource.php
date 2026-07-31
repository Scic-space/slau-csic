<?php

namespace App\Filament\Resources\FineTypes;

use App\Filament\Resources\FineTypes\Pages\ManageFineTypes;
use App\Models\FineType;
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
use Filament\Tables\Table;

class FineTypeResource extends Resource
{
    protected static ?string $model = FineType::class;

    protected static ?string $slug = 'manage-fine-types';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('default_amount')
                    ->numeric()
                    ->required(),
                Textarea::make('description'),
                Select::make('auto_apply_trigger')
                    ->options(FineType::getAutoApplyTriggers())
                    ->nullable(),
                TextInput::make('auto_apply_threshold')
                    ->numeric()
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('default_amount')
                    ->money('UGX')
                    ->sortable(),
                TextColumn::make('auto_apply_trigger')
                    ->badge(),
                IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => ManageFineTypes::route('/'),
        ];
    }
}
