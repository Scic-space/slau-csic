<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\FeedbackRelationManager;
use App\Filament\Resources\Events\RelationManagers\InstructorsRelationManager;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Resources\Events\RelationManagers\ResourcesRelationManager;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $slug = 'manage-events';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'workshop' => 'Workshop',
                        'competition' => 'Competition',
                        'ctf' => 'CTF',
                        'bootcamp' => 'Bootcamp',
                        'awareness_campaign' => 'Awareness Campaign',
                        'talk' => 'Talk',
                        'social' => 'Social',
                        'hackathon' => 'Hackathon',
                    ])
                    ->required(),
                RichEditor::make('description')
                    ->required(),
                FileUpload::make('banner_image')
                    ->image()
                    ->directory('events')
                    ->maxSize(2048),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required()
                    ->after('start_date'),
                TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                TextInput::make('external_link')
                    ->label('Meeting / registration link')
                    ->url()
                    ->maxLength(255),
                RichEditor::make('requirements')
                    ->label('Requirements / joining notes'),
                TextInput::make('max_participants')
                    ->numeric()
                    ->minValue(1)
                    ->label('Capacity'),
                Toggle::make('registration_required')
                    ->label('Registration Required')
                    ->default(true),
                TextInput::make('virtual_link')
                    ->label('Virtual Meeting Link (Zoom/Teams)')
                    ->url()
                    ->maxLength(255),
                FileUpload::make('gallery')
                    ->label('Gallery Images')
                    ->multiple()
                    ->image()
                    ->directory('events/gallery')
                    ->maxSize(2048),
                Toggle::make('waitlist_enabled')
                    ->label('Waitlist Enabled'),
                Toggle::make('is_public')
                    ->label('Public Event')
                    ->default(true),
                DateTimePicker::make('registration_deadline')
                    ->label('Registration Deadline'),
                Select::make('visibility')
                    ->options([
                        'members_only' => 'Members Only',
                        'public' => 'Public',
                        'invite_only' => 'Invite Only',
                    ])
                    ->default('members_only')
                    ->required(),
                Select::make('registration_type')
                    ->options([
                        'first_come' => 'First Come First Served',
                        'lottery' => 'Lottery',
                        'manual' => 'Manual Approval',
                    ])
                    ->default('first_come')
                    ->required(),
                RichEditor::make('learning_objectives')
                    ->label('Learning Objectives')
                    ->nullable(),
                Select::make('skill_level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                        'all' => 'All Levels',
                    ])
                    ->nullable(),
                Select::make('instructor_id')
                    ->relationship('instructor', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),
                TextInput::make('registration_fee')
                    ->label('Registration Fee ($)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('$'),

                TextInput::make('no_show_fine_amount')
                    ->label('No-Show Fine (UGX)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('UGX')
                    ->placeholder('e.g. 10000')
                    ->helperText('Leave empty to not fine no-shows. Registered members who miss this event are automatically fined this amount after it ends.'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('published')
                    ->required(),
                Select::make('organizer_id')
                    ->relationship('organizer', 'name')
                    ->default(Auth::id())
                    ->required(),
                Select::make('categories')
                    ->multiple()
                    ->relationship('categories', 'name'),
                Select::make('allowedMembers')
                    ->multiple()
                    ->relationship('allowedMembers', 'name')
                    ->label('Allowed Members (leave empty for public)')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_recurring')
                    ->label('Recurring Event'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('banner_image')
                    ->label('Image')
                    ->size(60)
                    ->circular()
                    ->defaultImageUrl(url('/images/events/default.jpg')),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'workshop' => 'primary',
                        'competition' => 'danger',
                        'ctf' => 'danger',
                        'bootcamp' => 'purple',
                        'awareness_campaign' => 'warning',
                        'talk' => 'info',
                        'social' => 'success',
                        'hackathon' => 'purple',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('max_participants')
                    ->label('Capacity')
                    ->sortable(),
                TextColumn::make('registered_count')
                    ->label('Registered')
                    ->getStateUsing(fn (Event $record): int => $record->registered_count),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'primary',
                        'scheduled' => 'info',
                        'ongoing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                TextColumn::make('organizer.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'workshop' => 'Workshop',
                        'competition' => 'Competition',
                        'ctf' => 'CTF',
                        'bootcamp' => 'Bootcamp',
                        'awareness_campaign' => 'Awareness Campaign',
                        'talk' => 'Talk',
                        'social' => 'Social',
                        'hackathon' => 'Hackathon',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('start_date')
                    ->schema([
                        DateTimePicker::make('start_date_from'),
                        DateTimePicker::make('start_date_to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->schema(fn (): array => static::form(app(Schema::class))->getComponents())
                        ->modalHeading('Event Details'),
                    EditAction::make()
                        ->schema(fn (): array => static::form(app(Schema::class))->getComponents()),
                    Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->label('Duplicate')
                        ->action(function (Event $record) {
                            $newEvent = $record->replicate();
                            $newEvent->title = $record->title.' (Copy)';
                            $newEvent->slug = null;
                            $newEvent->status = 'draft';
                            $newEvent->save();
                        }),
                    Action::make('mark_complete')
                        ->label('Mark Complete')
                        ->color('success')
                        ->visible(fn (Event $record): bool => $record->status === 'ongoing')
                        ->action(function (Event $record) {
                            $record->update(['status' => 'completed']);
                        }),
                    Action::make('attendance')
                        ->icon('heroicon-o-user-group')
                        ->label('Manage Attendance')
                        ->url(fn (Event $record): string => EditEvent::getUrl(['record' => $record])),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RegistrationsRelationManager::class,
            InstructorsRelationManager::class,
            ResourcesRelationManager::class,
            FeedbackRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
