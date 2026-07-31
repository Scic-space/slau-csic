<?php

namespace App\Filament\Resources\Elections\RelationManagers;

use App\Models\ElectionNomination;
use App\Notifications\NominationStatusNotification;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class NominationsRelationManager extends RelationManager
{
    protected static string $relationship = 'nominations';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'shortlisted' => 'Shortlisted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->required(),
                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->rows(4),
                TextInput::make('reviewer_id')
                    ->label('Reviewer ID'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->user->name).'&background=10b981&color=fff'),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')->badge()
                    ->colors([
                        'gray' => 'submitted',
                        'warning' => ['pending', 'under_review'],
                        'info' => 'shortlisted',
                        'success' => 'approved',
                        'danger' => ['rejected', 'withdrawn'],
                    ])
                    ->icons([
                        'heroicon-o-document-text' => 'submitted',
                        'heroicon-o-eye' => 'under_review',
                        'heroicon-o-star' => 'shortlisted',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-arrow-left-circle' => 'withdrawn',
                    ]),
                TextColumn::make('score_average')
                    ->label('Avg Score')
                    ->sortable()
                    ->toggleable()
                    ->default('—'),
                TextColumn::make('statement')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewer')
                    ->toggleable()
                    ->default('—'),
                TextColumn::make('interview_scheduled_at')
                    ->label('Interview')
                    ->dateTime()
                    ->toggleable()
                    ->default('—'),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'shortlisted' => 'Shortlisted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ]),
                SelectFilter::make('reviewer_id')
                    ->label('Reviewer')
                    ->relationship('reviewer', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema([
                        Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'shortlisted' => 'Shortlisted',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'withdrawn' => 'Withdrawn',
                            ])
                            ->disabled(),
                        TextInput::make('user.name')
                            ->label('Applicant')
                            ->disabled(),
                        Textarea::make('statement')
                            ->rows(3)
                            ->disabled(),
                        RichEditor::make('manifesto')
                            ->disabled(),
                        RichEditor::make('agenda')
                            ->disabled(),
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(4),
                        Select::make('reviewer.name')
                            ->label('Reviewed By')
                            ->disabled(),
                        TextInput::make('submitted_at')
                            ->label('Submitted At')
                            ->disabled(),
                        TextInput::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled(),
                        TextInput::make('interview_scheduled_at')
                            ->label('Interview')
                            ->disabled(),
                        TextInput::make('interview_location')
                            ->disabled(),
                    ]),
                TableAction::make('rate')
                    ->label('Rate')
                    ->color('info')
                    ->icon('heroicon-o-star')
                    ->form(function () {
                        $fields = [];
                        foreach (ElectionNomination::SCORE_CRITERIA as $key => $label) {
                            $fields[] = Select::make("scores.{$key}")
                                ->label($label)
                                ->options(array_combine(range(1, 5), range(1, 5)))
                                ->placeholder('—');
                        }

                        return $fields;
                    })
                    ->action(function (ElectionNomination $record, array $data) {
                        $record->update(['scores' => $data['scores'] ?? []]);

                        Notification::make()
                            ->success()
                            ->title('Scores saved')
                            ->body('Average: '.$record->fresh()->score_average.'/5')
                            ->send();
                    }),
                TableAction::make('scheduleInterview')
                    ->label('Schedule Interview')
                    ->color('purple')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        DateTimePicker::make('interview_scheduled_at')
                            ->label('Interview Date & Time')
                            ->required()
                            ->minDate(now()),
                        TextInput::make('interview_location')
                            ->label('Location / Meeting Link')
                            ->maxLength(255),
                        Textarea::make('interview_notes')
                            ->label('Notes for applicant')
                            ->rows(3),
                    ])
                    ->action(function (ElectionNomination $record, array $data) {
                        $record->update([
                            'interview_scheduled_at' => $data['interview_scheduled_at'],
                            'interview_location' => $data['interview_location'],
                            'interview_notes' => $data['interview_notes'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Interview scheduled')
                            ->send();
                    }),
                TableAction::make('viewReviews')
                    ->label('Review History')
                    ->color('gray')
                    ->icon('heroicon-o-clock')
                    ->modalHeading('Application Review History')
                    ->modalContent(function (ElectionNomination $record) {
                        $reviews = $record->reviews()->with('user')->latest()->get();

                        return view('admin.application-review-history', ['reviews' => $reviews]);
                    }),
                TableAction::make('markUnderReview')
                    ->label('Review')
                    ->color('warning')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (ElectionNomination $record): bool => $record->isSubmitted())
                    ->action(function (ElectionNomination $record) {
                        $record->markUnderReview(auth()->user());

                        activity()
                            ->performedOn($record->election)
                            ->causedBy(auth()->user())
                            ->withProperties(['nomination_id' => $record->id, 'user_id' => $record->user_id])
                            ->log('nomination_under_review');

                        $record->user->notify(new NominationStatusNotification(
                            $record->election, 'under_review'
                        ));

                        Notification::make()
                            ->success()
                            ->title('Application marked as under review')
                            ->send();
                    }),
                TableAction::make('shortlist')
                    ->label('Shortlist')
                    ->color('info')
                    ->icon('heroicon-o-star')
                    ->visible(fn (ElectionNomination $record): bool => in_array($record->status, ['submitted', 'under_review']))
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->action(function (ElectionNomination $record, array $data) {
                        $record->shortlist($data['admin_notes'] ?? null);

                        activity()
                            ->performedOn($record->election)
                            ->causedBy(auth()->user())
                            ->withProperties(['nomination_id' => $record->id, 'user_id' => $record->user_id])
                            ->log('nomination_shortlisted');

                        $record->user->notify(new NominationStatusNotification(
                            $record->election, 'shortlisted', $data['admin_notes'] ?? null
                        ));
                    }),
                TableAction::make('approve')
                    ->label('Approve & Create Candidate')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (ElectionNomination $record): bool => ! $record->isRejected() && ! $record->isWithdrawn())
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->action(function (ElectionNomination $record, array $data) {
                        $record->approve($data['admin_notes'] ?? null);

                        $record->election->candidates()->create([
                            'name' => $record->user->name,
                            'user_id' => $record->user_id,
                            'manifesto' => $record->manifesto,
                            'agenda' => $record->agenda,
                        ]);

                        activity()
                            ->performedOn($record->election)
                            ->causedBy(auth()->user())
                            ->withProperties(['nomination_id' => $record->id, 'user_id' => $record->user_id])
                            ->log('nomination_approved');

                        $record->user->notify(new NominationStatusNotification(
                            $record->election, 'approved', $data['admin_notes'] ?? null
                        ));

                        Notification::make()
                            ->success()
                            ->title('Candidate created from application')
                            ->body("{$record->user->name} is now a candidate")
                            ->send();
                    }),
                TableAction::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (ElectionNomination $record): bool => ! $record->isRejected() && ! $record->isWithdrawn())
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Reason for rejection')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (ElectionNomination $record, array $data) {
                        $record->reject($data['admin_notes']);

                        activity()
                            ->performedOn($record->election)
                            ->causedBy(auth()->user())
                            ->withProperties(['nomination_id' => $record->id, 'user_id' => $record->user_id])
                            ->log('nomination_rejected');

                        $record->user->notify(new NominationStatusNotification(
                            $record->election, 'rejected', $data['admin_notes']
                        ));
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkApprove')
                        ->label('Approve selected')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each(function (ElectionNomination $record) {
                            if (! $record->isRejected() && ! $record->isWithdrawn()) {
                                $record->approve();
                                $record->user->notify(new NominationStatusNotification($record->election, 'approved'));
                            }
                        })),
                    BulkAction::make('bulkReject')
                        ->label('Reject selected')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each(function (ElectionNomination $record) {
                            if (! $record->isRejected() && ! $record->isWithdrawn()) {
                                $record->reject('Bulk rejected');
                                $record->user->notify(new NominationStatusNotification($record->election, 'rejected'));
                            }
                        })),
                    BulkAction::make('bulkMarkUnderReview')
                        ->label('Mark as under review')
                        ->color('warning')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each(function (ElectionNomination $record) {
                            $record->markUnderReview(auth()->user());
                            $record->user->notify(new NominationStatusNotification($record->election, 'under_review'));
                        })),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
