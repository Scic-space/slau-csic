<?php

namespace App\Filament\Resources\QuestionBankQuestions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class QuestionBankQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('type')
                            ->options([
                                'mcq' => 'Multiple Choice',
                                'true_false' => 'True/False',
                                'short_answer' => 'Short Answer',
                                'code_snippet' => 'Code Snippet',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('marks')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ]),

                Textarea::make('question_text')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Textarea::make('code_block')
                    ->label('Code Block')
                    ->rows(6)
                    ->visible(fn (Get $get): bool => $get('type') === 'code_snippet')
                    ->columnSpanFull(),

                TextInput::make('code_language')
                    ->label('Code Language')
                    ->maxLength(50)
                    ->visible(fn (Get $get): bool => $get('type') === 'code_snippet'),

                Repeater::make('options')
                    ->relationship('options')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('option_text')
                                    ->label('Option')
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('is_correct')
                                    ->label('Correct')
                                    ->default(false),
                            ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['option_text'] ?? null)
                    ->minItems(2)
                    ->maxItems(6)
                    ->addActionLabel('Add Option')
                    ->visible(fn (Get $get): bool => $get('type') === 'mcq'),

                Select::make('correct_answer')
                    ->label('Correct Answer')
                    ->options([
                        'true' => 'True',
                        'false' => 'False',
                    ])
                    ->visible(fn (Get $get): bool => $get('type') === 'true_false'),

                Textarea::make('explanation')
                    ->label('Explanation (shown after answering)')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
