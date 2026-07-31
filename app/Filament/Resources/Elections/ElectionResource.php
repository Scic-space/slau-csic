<?php

namespace App\Filament\Resources\Elections;

use App\Exports\ElectionParticipationExport;
use App\Exports\ElectionResultsExport;
use App\Filament\Resources\Elections\Pages\CreateElection;
use App\Filament\Resources\Elections\Pages\EditElection;
use App\Filament\Resources\Elections\Pages\ListElections;
use App\Filament\Resources\Elections\RelationManagers\ActivityRelationManager;
use App\Filament\Resources\Elections\RelationManagers\CandidatesRelationManager;
use App\Filament\Resources\Elections\RelationManagers\NominationsRelationManager;
use App\Filament\Resources\Elections\RelationManagers\VotesRelationManager;
use App\Models\Election;
use App\Models\User;
use App\Notifications\ElectionClosedNotification;
use App\Notifications\ElectionOpenedNotification;
use App\Notifications\ElectionResultsPublishedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ElectionResource extends Resource
{
    protected static ?string $model = Election::class;

    protected static ?string $slug = 'manage-elections';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('position')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description'),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'suspended' => 'Suspended',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at')
                    ->afterOrEqual('starts_at'),
                Toggle::make('results_visible'),
                Toggle::make('allow_vote_changes')
                    ->label('Allow vote changes')
                    ->default(true),
                Toggle::make('is_test_ballot')
                    ->label('Test ballot (dry-run)')
                    ->helperText('Test votes will not count toward official results')
                    ->default(false),
                DateTimePicker::make('results_publish_at')
                    ->label('Schedule results publication')
                    ->afterOrEqual('ends_at')
                    ->helperText('If set, results will auto-publish at this time'),
                DateTimePicker::make('applications_starts_at')
                    ->label('Applications start'),
                DateTimePicker::make('applications_ends_at')
                    ->label('Applications end')
                    ->afterOrEqual('applications_starts_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'open',
                        'warning' => 'suspended',
                        'danger' => 'closed',
                    ]),
                IconColumn::make('is_test_ballot')
                    ->boolean()
                    ->label('Test')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip('Test/dry-run ballot'),
                TextColumn::make('candidates_count')
                    ->counts('candidates')
                    ->label('Candidates')
                    ->sortable(),
                TextColumn::make('votes_count')
                    ->counts('votes')
                    ->label('Votes')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('results_publish_at')
                    ->label('Results scheduled')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                ToggleColumn::make('allow_vote_changes'),
                ToggleColumn::make('results_visible'),
                TextColumn::make('turnout')
                    ->label('Turnout')
                    ->getStateUsing(fn (Election $record): string => $record->getTurnoutAttribute()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'suspended' => 'Suspended',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('is_test_ballot')
                    ->label('Test ballot')
                    ->options([
                        '0' => 'Live elections',
                        '1' => 'Test ballots only',
                    ]),
                TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (Election $record): bool => in_array($record->status, ['draft', 'suspended']))
                    ->action(function (Election $record) {
                        $record->update([
                            'status' => 'open',
                            'starts_at' => $record->starts_at ?? now(),
                            'ends_at' => $record->ends_at ?? now()->addDays(7),
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('election_opened');

                        User::activeMembers()->chunk(100, fn ($users) => $users->each(
                            fn ($user) => $user->notify(new ElectionOpenedNotification($record))
                        ));
                    }),
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Election $record): bool => $record->status === 'open')
                    ->action(function (Election $record) {
                        $record->update(['status' => 'suspended']);
                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('election_suspended');
                    }),
                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (Election $record): bool => $record->status === 'open')
                    ->action(function (Election $record) {
                        $record->update(['status' => 'closed', 'ends_at' => now()]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('election_closed');

                        User::whereHas('electionVotes', fn ($q) => $q->where('election_id', $record->id))
                            ->chunk(100, fn ($users) => $users->each(
                                fn ($user) => $user->notify(new ElectionClosedNotification($record))
                            ));
                    }),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Election $record) {
                        $clone = $record->replicate()->fill([
                            'title' => $record->title.' (Copy)',
                            'slug' => $record->slug.'-copy-'.Str::random(4),
                            'status' => 'draft',
                            'results_visible' => false,
                            'starts_at' => null,
                            'ends_at' => null,
                            'results_publish_at' => null,
                        ]);
                        $clone->save();

                        foreach ($record->candidates as $candidate) {
                            $clone->candidates()->create($candidate->only([
                                'name', 'user_id', 'manifesto', 'agenda', 'sort_order',
                            ]));
                        }

                        Notification::make()
                            ->title("Election duplicated as \"{$clone->title}\"")
                            ->success()
                            ->send();
                    }),
                Action::make('view_non_voters')
                    ->label('Non-Voters')
                    ->icon('heroicon-o-user-group')
                    ->color('gray')
                    ->visible(fn (Election $record): bool => $record->status !== 'draft')
                    ->action(function (Election $record) {
                        $voterIds = $record->votes()->pluck('user_id');
                        $nonVoters = User::activeMembers()
                            ->whereNotIn('id', $voterIds)
                            ->limit(50)
                            ->get(['id', 'name', 'email']);

                        $names = $nonVoters->map(fn ($u) => "- {$u->name} ({$u->email})")->join("\n");

                        Notification::make()
                            ->title($nonVoters->count().' active members have not voted')
                            ->body($names ?: 'All active members have voted.')
                            ->persistent()
                            ->send();
                    }),
                ActionGroup::make([
                    Action::make('export_results_excel')
                        ->label('Results (XLSX)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->visible(fn (Election $record): bool => $record->status === 'closed')
                        ->action(fn (Election $record) => Excel::download(
                            new ElectionResultsExport($record),
                            Str::slug($record->title).'-results.xlsx'
                        )),
                    Action::make('export_results_pdf')
                        ->label('Results (PDF)')
                        ->icon('heroicon-o-document-text')
                        ->color('danger')
                        ->visible(fn (Election $record): bool => $record->status === 'closed')
                        ->action(function (Election $record) {
                            $record->loadMissing(['candidates.votes']);
                            $totalVotes = $record->votes()->count();
                            $pdf = Pdf::loadView('exports.election-results', [
                                'election' => $record,
                                'totalVotes' => $totalVotes,
                            ]);

                            return response()->streamDownload(
                                fn () => print ($pdf->output()),
                                Str::slug($record->title).'-results.pdf'
                            );
                        }),
                    Action::make('export_participation')
                        ->label('Voters list (XLSX)')
                        ->icon('heroicon-o-users')
                        ->color('info')
                        ->visible(fn (Election $record): bool => $record->status !== 'draft')
                        ->action(fn (Election $record) => Excel::download(
                            new ElectionParticipationExport($record),
                            Str::slug($record->title).'-voters.xlsx'
                        )),
                ])->label('Export')->icon('heroicon-o-document-arrow-down'),
                Action::make('publish_results')
                    ->label('Publish Results')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->visible(fn (Election $record): bool => $record->status === 'closed' && ! $record->results_visible)
                    ->action(function (Election $record) {
                        $record->update(['results_visible' => true]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(auth()->user())
                            ->log('election_results_published');

                        User::whereHas('electionVotes', fn ($q) => $q->where('election_id', $record->id))
                            ->chunk(100, fn ($users) => $users->each(
                                fn ($user) => $user->notify(new ElectionResultsPublishedNotification($record))
                            ));
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\Action::make('bulk_open')
                    ->label('Open selected')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->accessSelectedRecords()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (\Illuminate\Support\Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (in_array($record->status, ['draft', 'suspended'])) {
                                $record->update(['status' => 'open', 'starts_at' => $record->starts_at ?? now()]);
                                $count++;
                            }
                        }
                        Notification::make()->title("Opened {$count} election(s).")->success()->send();
                    }),
                \Filament\Actions\Action::make('bulk_close')
                    ->label('Close selected')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->accessSelectedRecords()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (\Illuminate\Support\Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'open') {
                                $record->update(['status' => 'closed']);
                                $count++;
                            }
                        }
                        Notification::make()->title("Closed {$count} election(s).")->success()->send();
                    }),
                DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CandidatesRelationManager::class,
            VotesRelationManager::class,
            NominationsRelationManager::class,
            \App\Filament\Resources\Elections\RelationManagers\VoterEligibilityRelationManager::class,
            ActivityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListElections::route('/'),
            'create' => CreateElection::route('/create'),
            'edit' => EditElection::route('/{record}/edit'),
        ];
    }
}
