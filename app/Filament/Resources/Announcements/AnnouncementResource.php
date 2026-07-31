<?php

namespace App\Filament\Resources\Announcements;

use App\Filament\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $slug = 'manage-announcements';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated(false),
                RichEditor::make('content')
                    ->required(),
                Select::make('type')
                    ->options([
                        'general' => 'General',
                        'event' => 'Event',
                        'meeting' => 'Meeting',
                        'urgent' => 'Urgent',
                        'achievement' => 'Achievement',
                    ])
                    ->required(),
                Select::make('audience')
                    ->options([
                        'all' => 'All Members',
                        'members' => 'Members Only',
                        'admins' => 'Admins Only',
                        'specific_roles' => 'Specific Roles',
                    ])
                    ->required(),
                TagsInput::make('target_roles'),
                Toggle::make('is_published')
                    ->default(false),
                Toggle::make('send_email')
                    ->default(false),
                Toggle::make('send_push')
                    ->default(false),
                DateTimePicker::make('published_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('type')->badge()
                    ->colors([
                        'gray' => 'general',
                        'success' => 'event',
                        'info' => 'meeting',
                        'danger' => 'urgent',
                        'warning' => 'achievement',
                    ]),
                TextColumn::make('audience')->badge(),
                IconColumn::make('is_published')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Author'),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'general' => 'General',
                        'event' => 'Event',
                        'meeting' => 'Meeting',
                        'urgent' => 'Urgent',
                        'achievement' => 'Achievement',
                    ]),
                Filter::make('is_published')
                    ->query(fn ($query) => $query->where('is_published', true)),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ManageAnnouncements::route('/'),
        ];
    }
}
