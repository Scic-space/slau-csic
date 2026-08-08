<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeetingForm
{
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
                            ->required(),

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
                            ->default(false),
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
