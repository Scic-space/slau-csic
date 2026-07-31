<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', str($state)->slug())),

                        Select::make('category')
                            ->required()
                            ->options([
                                'ethical_hacking' => 'Ethical Hacking',
                                'digital_forensics' => 'Digital Forensics',
                                'network_security' => 'Network Security',
                                'web_security' => 'Web Security',
                                'mobile_security' => 'Mobile Security',
                                'ctf' => 'CTF',
                                'programming' => 'Programming',
                                'other' => 'Other',
                            ]),
                    ]),

                Textarea::make('description')
                    ->required()
                    ->rows(3),

                Grid::make(3)
                    ->schema([
                        Select::make('difficulty')
                            ->required()
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ]),

                        TextInput::make('duration_hours')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('hours'),

                        TextInput::make('max_enrollments')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Unlimited'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Textarea::make('objectives')
                            ->rows(3)
                            ->placeholder('What participants will learn...'),

                        Textarea::make('prerequisites')
                            ->rows(3)
                            ->placeholder('Required knowledge or skills...'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('instructor_id')
                            ->label('Instructor')
                            ->options(fn () => User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'super-admin', 'teacher']))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable(),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail Image')
                            ->image()
                            ->directory('trainings')
                            ->maxSize(2048),
                    ]),

                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('available_from')
                            ->label('Available From'),

                        DateTimePicker::make('available_until')
                            ->label('Available Until')
                            ->afterOrEqual('available_from'),
                    ]),

                Toggle::make('is_published')
                    ->label('Published'),
            ]);
    }
}
