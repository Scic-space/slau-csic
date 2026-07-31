<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\ExamQuestion;
use App\Models\QuestionBankQuestion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question_text')
                    ->label('Question')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'code_snippet' => 'Code Snippet',
                    ])
                    ->required()
                    ->live(),

                TextInput::make('marks')
                    ->label('Default Marks')
                    ->numeric()
                    ->default(10)
                    ->required(),

                TextInput::make('custom_marks')
                    ->label('Custom Marks (override)')
                    ->numeric()
                    ->nullable()
                    ->helperText('Leave empty to use default marks'),

                Textarea::make('explanation')
                    ->label('Explanation')
                    ->rows(3)
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->label('Answer Options')
                    ->schema([
                        TextInput::make('option_text')
                            ->label('Option')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_correct')
                            ->label('Correct Answer')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->visible(fn ($get) => in_array($get('type'), ['multiple_choice', 'true_false', 'code_snippet']))
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->directory('question-images')
                    ->disk('public')
                    ->visibility('public')
                    ->nullable()
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('type') === 'code_snippet'),

                Textarea::make('code_block')
                    ->label('Code Block')
                    ->rows(6)
                    ->nullable()
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('type') === 'code_snippet'),

                Select::make('code_language')
                    ->label('Code Language')
                    ->options([
                        'php' => 'PHP',
                        'javascript' => 'JavaScript',
                        'python' => 'Python',
                        'java' => 'Java',
                        'cpp' => 'C++',
                        'c' => 'C',
                        'html' => 'HTML',
                        'css' => 'CSS',
                        'sql' => 'SQL',
                        'bash' => 'Bash',
                        'ruby' => 'Ruby',
                        'go' => 'Go',
                        'rust' => 'Rust',
                        'typescript' => 'TypeScript',
                        'other' => 'Other',
                    ])
                    ->nullable()
                    ->visible(fn ($get) => $get('type') === 'code_snippet'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('question.type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True/False',
                        'short_answer' => 'Short Answer',
                        'code_snippet' => 'Code Snippet',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'info',
                        'true_false' => 'warning',
                        'short_answer' => 'success',
                        'code_snippet' => 'danger',
                        default => 'gray',
                    }),

                ImageColumn::make('question.image')
                    ->label('Image')
                    ->disk('public')
                    ->size(60)
                    ->square(),

                TextColumn::make('effective_marks')
                    ->label('Marks')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Question')
                    ->using(function (array $data, self $livewire): ExamQuestion {
                        $exam = $livewire->getOwnerRecord();

                        $imagePath = null;
                        if (! empty($data['image'])) {
                            $imagePath = $data['image'];
                        }

                        $questionBank = QuestionBankQuestion::create([
                            'user_id' => auth()->id(),
                            'type' => $data['type'],
                            'question_text' => $data['question_text'],
                            'code_block' => $data['code_block'] ?? null,
                            'code_language' => $data['code_language'] ?? null,
                            'image' => $imagePath,
                            'marks' => $data['marks'],
                            'explanation' => $data['explanation'] ?? null,
                        ]);

                        if (! empty($data['options'])) {
                            foreach ($data['options'] as $i => $option) {
                                $questionBank->options()->create([
                                    'option_text' => $option['option_text'],
                                    'is_correct' => $option['is_correct'] ?? false,
                                    'order' => $i,
                                ]);
                            }
                        }

                        $maxOrder = $exam->questions()->max('order') ?? -1;

                        return $exam->examQuestions()->create([
                            'question_bank_question_id' => $questionBank->id,
                            'custom_marks' => $data['custom_marks'] ? (int) $data['custom_marks'] : null,
                            'order' => $maxOrder + 1,
                        ]);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function (ExamQuestion $record, array $data): ExamQuestion {
                        $record->update([
                            'custom_marks' => $data['custom_marks'] ? (int) $data['custom_marks'] : null,
                        ]);

                        $questionBank = $record->question;
                        if ($questionBank) {
                            $oldImage = $questionBank->image;

                            $questionBank->update([
                                'type' => $data['type'],
                                'question_text' => $data['question_text'],
                                'code_block' => $data['code_block'] ?? null,
                                'code_language' => $data['code_language'] ?? null,
                                'image' => $data['image'] ?? $oldImage,
                                'marks' => $data['marks'],
                                'explanation' => $data['explanation'] ?? null,
                            ]);

                            if (! empty($data['image']) && $data['image'] !== $oldImage && $oldImage) {
                                Storage::disk('public')->delete($oldImage);
                            }

                            $questionBank->options()->delete();
                            if (! empty($data['options'])) {
                                foreach ($data['options'] as $i => $option) {
                                    $questionBank->options()->create([
                                        'option_text' => $option['option_text'],
                                        'is_correct' => $option['is_correct'] ?? false,
                                        'order' => $i,
                                    ]);
                                }
                            }
                        }

                        return $record;
                    })
                    ->mutateRecordDataUsing(function (ExamQuestion $record): array {
                        $question = $record->question;

                        return [
                            'question_text' => $question?->question_text ?? '',
                            'type' => $question?->type ?? 'multiple_choice',
                            'marks' => $question?->marks ?? 10,
                            'custom_marks' => $record->custom_marks,
                            'explanation' => $question?->explanation ?? '',
                            'image' => $question?->image,
                            'code_block' => $question?->code_block,
                            'code_language' => $question?->code_language,
                            'options' => $question?->options->map(fn ($o) => [
                                'option_text' => $o->option_text,
                                'is_correct' => $o->is_correct,
                            ])->toArray() ?? [],
                        ];
                    }),

                DeleteAction::make()
                    ->using(function (ExamQuestion $record): void {
                        $image = $record->question?->image;
                        $record->question?->options()->delete();
                        $record->delete();
                        if ($image) {
                            Storage::disk('public')->delete($image);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
