<?php

namespace App\Filament\Resources\CtfCategories;

use App\Filament\Resources\CtfCategories\Pages\ManageCtfCategories;
use App\Models\CtfCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CtfCategoryResource extends Resource
{
    protected static ?string $model = CtfCategory::class;

    protected static ?string $slug = 'manage-ctf-categories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from name'),
                ColorPicker::make('color'),
                TextInput::make('icon'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug'),
                ColorColumn::make('color'),
                TextColumn::make('icon'),
                TextColumn::make('challenges_count')
                    ->counts('challenges')
                    ->label('Challenges'),
                TextColumn::make('sort_order')
                    ->sortable(),
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
            'index' => ManageCtfCategories::route('/'),
        ];
    }
}
