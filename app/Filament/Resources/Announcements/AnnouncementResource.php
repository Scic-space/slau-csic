<?php

namespace App\Filament\Resources\Announcements;

use App\Filament\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use Filament\Actions\Action;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $slug = 'manage-announcements';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->extraAttributes(['class' => 'announcement-form'])
            ->components([
                Section::make(self::sectionHeading('edit_note', 'Announcement details'))
                    ->description('Add a clear title, web address, and the full announcement message.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('title')
                            ->label('Announcement title')
                            ->placeholder('Enter a clear announcement title')
                            ->prefix(self::materialIcon('title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get, ?Announcement $record): void {
                                if ($record || filled($get('slug'))) {
                                    return;
                                }

                                $set('slug', Str::slug($state ?? ''));
                            }),
                        TextInput::make('slug')
                            ->label('URL slug')
                            ->placeholder('announcement-url-slug')
                            ->prefix(self::materialIcon('link'))
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->afterStateHydrated(function (?string $state, callable $set, ?Announcement $record): void {
                                if (blank($state) && $record) {
                                    $set('slug', Announcement::generateUniqueSlug($record->title));
                                }
                            })
                            ->helperText('Generated from the title. You can edit it using lowercase letters, numbers, and hyphens.'),
                        RichEditor::make('content')
                            ->label('Announcement content')
                            ->helperText('Format the message with headings, lists, links, and emphasis where helpful.')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make(self::sectionHeading('tune', 'Publishing settings'))
                    ->description('Choose the announcement category, audience, and publishing window.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Select::make('type')
                            ->label('Announcement type')
                            ->placeholder('Select announcement type')
                            ->options([
                                'general' => 'General',
                                'event' => 'Event',
                                'meeting' => 'Meeting',
                                'urgent' => 'Urgent',
                                'achievement' => 'Achievement',
                            ])
                            ->required(),
                        Select::make('audience')
                            ->label('Target audience')
                            ->placeholder('Select target audience')
                            ->options([
                                'all' => 'All Members',
                                'active_members' => 'Active Members',
                                'board' => 'Board Members',
                                'specific_roles' => 'Specific Roles',
                            ])
                            ->required(),
                        TagsInput::make('target_roles')
                            ->label('Target roles')
                            ->placeholder('Add a role and press Enter')
                            ->helperText('Used when the target audience is set to specific roles.')
                            ->columnSpanFull(),
                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->placeholder('Choose a publish date and time'),
                        DateTimePicker::make('expires_at')
                            ->label('Expiry date')
                            ->placeholder('Choose an optional expiry date and time'),
                    ]),
                Section::make(self::sectionHeading('notifications_active', 'Delivery options'))
                    ->description('Control publication and the existing announcement delivery channels.')
                    ->columns([
                        'default' => 1,
                        'sm' => 3,
                    ])
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Make this announcement visible.')
                            ->default(false),
                        Toggle::make('send_email')
                            ->label('Send email')
                            ->helperText('Notify recipients by email.')
                            ->default(false),
                        Toggle::make('send_push')
                            ->label('Send push')
                            ->helperText('Send a push notification.')
                            ->default(false),
                    ]),
            ]);
    }

    private static function sectionHeading(string $icon, string $label): HtmlString
    {
        return new HtmlString('<span class="announcement-form-heading"><span class="material-symbols-outlined" aria-hidden="true">'.e($icon).'</span><span>'.e($label).'</span></span>');
    }

    private static function materialIcon(string $icon): HtmlString
    {
        return new HtmlString('<span class="material-symbols-outlined announcement-field-icon" aria-hidden="true">'.e($icon).'</span>');
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
                EditAction::make()
                    ->label('Edit')
                    ->icon(self::materialIcon('edit'))
                    ->modalHeading('Edit announcement')
                    ->modalDescription('Update the announcement while keeping its existing publication settings and web address unless changed.')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(fn (Action $action): Action => $action
                        ->label('Update Announcement')
                        ->icon(self::materialIcon('save'))
                        ->extraAttributes(['class' => 'announcement-primary-action']))
                    ->modalCancelAction(fn (Action $action): Action => $action
                        ->label('Cancel')
                        ->icon(self::materialIcon('close'))),
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
