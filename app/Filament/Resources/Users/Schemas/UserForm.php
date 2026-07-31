<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership Summary')
                    ->columns(4)
                    ->schema([
                        Placeholder::make('member_since')
                            ->label('Member Since')
                            ->content(fn ($record) => $record?->joined_at?->format('M d, Y') ?? '—'),

                        Placeholder::make('days_active')
                            ->label('Days as Member')
                            ->content(fn ($record) => $record?->joined_at ? $record->joined_at->diffInDays(now()).' days' : '—'),

                        Placeholder::make('attendance_rate')
                            ->label('Attendance Rate')
                            ->content(fn ($record) => $record?->getAttendanceRate().'%'),

                        Placeholder::make('current_streak')
                            ->label('Current Streak')
                            ->content(fn ($record) => $record?->current_streak.' meetings'),

                        Placeholder::make('total_attendance')
                            ->label('Total Meetings')
                            ->content(fn ($record) => $record?->total_sessions_attended ?? 0),

                        Placeholder::make('membership_expiry')
                            ->label('Expiry Status')
                            ->content(function ($record) {
                                if (! $record?->membership_expires_at) {
                                    return 'Not set';
                                }

                                if ($record->membership_expires_at->isPast()) {
                                    return 'Expired '.$record->membership_expires_at->diffForHumans();
                                }

                                return 'Expires '.$record->membership_expires_at->diffForHumans();
                            }),

                        Placeholder::make('last_renewed')
                            ->label('Last Renewed')
                            ->content(fn ($record) => $record?->membership_renewed_at?->format('M d, Y') ?? 'N/A'),

                        Placeholder::make('score')
                            ->label('Score')
                            ->content(fn ($record) => $record?->score ?? 0),
                    ])
                    ->visible(fn ($record) => $record !== null),

                FileUpload::make('profile_photo')
                    ->label('Profile Photo')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('profile-photos')
                    ->avatar()
                    ->circleCropper()
                    ->maxSize(5120),

                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('registration_number')
                            ->label('Registration Number')
                            ->helperText('e.g. BACS/24D/U/A0160')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->regex('/^[A-Za-z]+\/\d{2}[DW]\/[A-Za-z]\/[A-Za-z]\d+$/'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('program')
                            ->label('Program/Course')
                            ->maxLength(100),

                        Select::make('year_of_study')
                            ->label('Year of Study')
                            ->options([
                                1 => 'Year 1',
                                2 => 'Year 2',
                                3 => 'Year 3',
                                4 => 'Year 4',
                                5 => 'Year 5',
                            ]),

                        Select::make('membership_type')
                            ->label('Membership Type')
                            ->options([
                                'active' => 'Active Member',
                                'associate' => 'Associate Member',
                                'alumni' => 'Alumni',
                            ])
                            ->required(),

                        Select::make('membership_status')
                            ->label('Membership Status')
                            ->options([
                                'active' => 'Active',
                                'pending' => 'Pending Approval',
                                'suspended' => 'Suspended',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),

                        DatePicker::make('joined_at')
                            ->label('Joined Date')
                            ->default(now()),

                        Select::make('roles')
                            ->label('Assign Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),

                        TextInput::make('password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->revealable(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('faculty')
                            ->maxLength(100),

                        TextInput::make('residence')
                            ->maxLength(255),

                        TextInput::make('github_username')
                            ->maxLength(255),

                        TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('discord_username')
                            ->maxLength(255),

                        TextInput::make('headline')
                            ->maxLength(255),
                    ]),

                Textarea::make('bio')
                    ->rows(3),

                Textarea::make('approval_notes')
                    ->label('Approval/Rejection Notes')
                    ->rows(2),

                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->rows(3)
                    ->helperText('Private notes visible only to admins')
                    ->columnSpanFull(),

                DatePicker::make('membership_expires_at')
                    ->label('Membership Expires')
                    ->native(false),

                DateTimePicker::make('membership_renewed_at')
                    ->label('Last Renewed')
                    ->native(false),

                CheckboxList::make('privacy_settings')
                    ->label('Privacy Settings')
                    ->nullable()
                    ->options([
                        'show_email' => 'Show Email',
                        'show_phone' => 'Show Phone',
                        'show_discord' => 'Show Discord',
                        'show_attendance' => 'Show Attendance Stats',
                        'show_program' => 'Show Program',
                        'show_year' => 'Show Year of Study',
                        'allow_contact' => 'Allow Contact Form',
                        'show_profile' => 'Show in Public Directory',
                    ])
                    ->columns(2)
                    ->default([
                        'show_program',
                        'show_year',
                        'allow_contact',
                        'show_profile',
                    ])
                    ->stateCast(new class implements StateCast
                    {
                        private const KNOWN_KEYS = ['show_email', 'show_phone', 'show_discord', 'show_attendance', 'show_program', 'show_year', 'allow_contact', 'show_profile'];

                        public function get(mixed $state): mixed
                        {
                            return is_array($state) ? $state : [];
                        }

                        public function set(mixed $state): mixed
                        {
                            if (! is_array($state)) {
                                return [];
                            }

                            if (array_is_list($state)) {
                                return $state;
                            }

                            $state = array_intersect_key($state, array_flip(self::KNOWN_KEYS));

                            return array_keys(array_filter($state));
                        }
                    }),
            ]);
    }
}
