<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Models\Training;
use App\Models\TrainingEnrollment;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class GradeBook extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = \App\Filament\Resources\Trainings\TrainingResource::class;

    protected static ?string $title = 'Grade Book';

    protected string $view = 'filament.resources.trainings.pages.grade-book';

    protected static ?string $navigationLabel = 'Grade Book';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 3;

    public function getRecord(): Training
    {
        return Training::findOrFail($this->record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrainingEnrollment::query()
                    ->where('training_id', $this->getRecord()->id)
                    ->with('user')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'enrolled',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ]),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : '0%'),
                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state}%" : '-')
                    ->sortable(),
                TextColumn::make('enrolled_at')
                    ->label('Enrolled')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->nullable(),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->recordActions([
                \Filament\Actions\Action::make('update_score')
                    ->label('Set Score')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form([
                        TextInput::make('score')
                            ->label('Score (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])
                    ->action(function (TrainingEnrollment $record, array $data): void {
                        $record->update([
                            'score' => $data['score'],
                        ]);

                        Notification::make()
                            ->title('Score updated')
                            ->body("Score set to {$data['score']}% for {$record->user->name}")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
