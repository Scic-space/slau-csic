<?php

namespace App\Filament\Resources\System;

use App\Models\Setting;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SettingsResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'key';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Setting $record): bool => $record !== null),
                Select::make('group')
                    ->options([
                        'general' => 'General',
                        'membership' => 'Membership',
                        'features' => 'Features',
                        'notifications' => 'Notifications',
                        'integrations' => 'Integrations',
                    ])
                    ->required(),
                Select::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'json' => 'JSON',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->visible(fn ($get): bool => ! in_array($get('type'), ['boolean', 'text', 'json']))
                    ->required(fn ($get): bool => ! in_array($get('type'), ['boolean', 'text', 'json'])),
                Toggle::make('value')
                    ->label('Value (Enabled)')
                    ->visible(fn ($get): bool => $get('type') === 'boolean'),
                Textarea::make('value')
                    ->visible(fn ($get): bool => in_array($get('type'), ['text', 'json']))
                    ->rows(3),
                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('value')
                    ->limit(40),
                TextColumn::make('group')
                    ->badge()
                    ->colors([
                        'gray' => 'general',
                        'info' => 'membership',
                        'success' => 'features',
                        'warning' => 'notifications',
                        'primary' => 'integrations',
                    ]),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'membership' => 'Membership',
                        'features' => 'Features',
                        'notifications' => 'Notifications',
                        'integrations' => 'Integrations',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'json' => 'JSON',
                    ]),
            ])
            ->defaultSort('group', 'asc')
            ->searchable()
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
            'index' => \App\Filament\Resources\System\Pages\ListSettings::route('/'),
            'create' => \App\Filament\Resources\System\Pages\CreateSettings::route('/create'),
            'edit' => \App\Filament\Resources\System\Pages\EditSettings::route('/{record}/edit'),
        ];
    }
}
