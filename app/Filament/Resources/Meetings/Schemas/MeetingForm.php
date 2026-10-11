<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MeetingForm
{
    protected static function isPastScheduled(mixed $value): bool
    {
        if (blank($value)) {
            return false;
        }

        try {
            return Carbon::parse($value)->isPast();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return array<int, string> Member ID to "Name (Y2)" labels.
     */
    public static function memberOptions(?Meeting $meeting = null): array
    {
        $users = ($meeting && $meeting->allowedAttendees()->exists())
            ? $meeting->allowedAttendees()->with('memberProfile')->get()
            : User::activeMembers()->with('memberProfile')->get();

        return $users
            ->sortBy('name')
            ->mapWithKeys(fn (User $user) => [
                $user->id => $user->name.($user->memberProfile?->year_of_study
                    ? " (Y{$user->memberProfile->year_of_study})"
                    : ''),
            ])
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function memberIdsForYear(int $year): array
    {
        return User::activeMembers()
            ->whereHas('memberProfile', fn ($query) => $query->where('year_of_study', $year))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function memberIdsAboveYear(int $year): array
    {
        return User::activeMembers()
            ->whereHas('memberProfile', fn ($query) => $query->where('year_of_study', '>', $year))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Quick-select actions (Year 1 / Year 2+ / Clear) for an attendance picker.
     *
     * @return list<Action>
     */
    public static function attendanceBulkActions(string $field): array
    {
        return [
            Action::make("{$field}_year_1")
                ->label('Y1')
                ->action(fn (Set $set) => $set($field, self::memberIdsForYear(1))),
            Action::make("{$field}_year_above")
                ->label('Y2+')
                ->action(fn (Set $set) => $set($field, self::memberIdsAboveYear(1))),
            Action::make("{$field}_clear")
                ->label('Clear')
                ->action(fn (Set $set) => $set($field, [])),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meeting Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'general' => 'General',
                                'executive' => 'Executive',
                                'special' => 'Special',
                                'training' => 'Training',
                                'workshop' => 'Workshop',
                                'teaching_session' => 'Teaching Session',
                            ])
                            ->required(),

                        DateTimePicker::make('scheduled_at')
                            ->label('Scheduled At')
                            ->required()
                            ->live(),

                        TextInput::make('location')
                            ->maxLength(255),

                        TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->default(60),

                        TextInput::make('expected_attendees')
                            ->label('Expected Attendees')
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('missed_fine_amount')
                            ->label('Missed Meeting Fine (UGX)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('UGX')
                            ->placeholder('e.g. 5000')
                            ->helperText('Leave empty to not fine absentees. Members who miss this meeting are automatically fined this amount after it ends.'),

                        TextInput::make('late_threshold_minutes')
                            ->label('Late Threshold (minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->default(15),

                        TextInput::make('meeting_code')
                            ->label('Meeting Code')
                            ->maxLength(20)
                            ->helperText('Leave blank to auto-generate'),

                        TextInput::make('meeting_link')
                            ->label('Google Meet Link')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx'),

                        TextInput::make('code_expires_minutes')
                            ->label('Code Expires (minutes)')
                            ->numeric()
                            ->minValue(1),

                        Select::make('created_by')
                            ->relationship('creator', 'name')
                            ->default(auth()->id())
                            ->required(),

                        Toggle::make('is_recurring')
                            ->label('Mark as recurring (master)')
                            ->default(false)
                            ->live(),

                        Select::make('recurrence_pattern')
                            ->label('Recurrence Pattern')
                            ->options([
                                'weekly' => 'Weekly',
                                'biweekly' => 'Every 2 Weeks',
                                'monthly' => 'Monthly',
                            ])
                            ->default('weekly')
                            ->required(fn (Get $get): bool => (bool) $get('is_recurring'))
                            ->visible(fn (Get $get): bool => (bool) $get('is_recurring')),

                        TextInput::make('recurrence_interval')
                            ->label('Repeat Every')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->suffix(fn (Get $get): string => match ($get('recurrence_pattern')) {
                                'monthly' => 'month(s)',
                                'biweekly' => 'fortnight(s)',
                                default => 'week(s)',
                            })
                            ->visible(fn (Get $get): bool => (bool) $get('is_recurring')),

                        DatePicker::make('recurrence_ends_at')
                            ->label('Repeat Until (optional)')
                            ->minDate(now()->toDateString())
                            ->visible(fn (Get $get): bool => (bool) $get('is_recurring')),
                    ]),

                Section::make('Past Attendance')
                    ->description('Only for classes that already happened. Select who attended; everyone else is marked absent and the meeting is completed on save. Leave empty to record attendance later.')
                    ->visible($schema->getOperation() === 'create')
                    ->schema([
                        CheckboxList::make('present_ids')
                            ->label('Present')
                            ->options(fn (): array => self::memberOptions())
                            ->searchable()
                            ->columns(2)
                            ->hintActions(self::attendanceBulkActions('present_ids'))
                            ->visible(fn (Get $get): bool => self::isPastScheduled($get('scheduled_at'))),
                        CheckboxList::make('late_ids')
                            ->label('Late')
                            ->options(fn (): array => self::memberOptions())
                            ->searchable()
                            ->columns(2)
                            ->hintActions(self::attendanceBulkActions('late_ids'))
                            ->visible(fn (Get $get): bool => self::isPastScheduled($get('scheduled_at'))),
                    ]),

                Section::make('Description & Agenda')
                    ->schema([
                        RichEditor::make('description')
                            ->columnSpanFull(),

                        Textarea::make('agenda')
                            ->label('Agenda (plain text)')
                            ->helperText('For structured items with presenters and time allocations, use the Agenda Items tab after creating the meeting.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Minutes')
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        RichEditor::make('minutes')
                            ->columnSpanFull(),

                        Select::make('minutes_status')
                            ->options([
                                'draft' => 'Draft',
                                'finalized' => 'Finalized',
                                'published' => 'Published',
                            ])
                            ->default('draft'),
                    ]),
            ]);
    }
}
