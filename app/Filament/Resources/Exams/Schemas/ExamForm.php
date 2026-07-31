<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),

                        TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(60),

                        TextInput::make('passing_score')
                            ->label('Passing Score (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(50)
                            ->suffix('%'),

                        Select::make('user_id')
                            ->label('Creator')
                            ->relationship('creator', 'name')
                            ->default(auth()->id())
                            ->required(),
                    ]),

                RichEditor::make('description')
                    ->label('Description / Instructions')
                    ->columnSpanFull(),
            ]);
    }
}
