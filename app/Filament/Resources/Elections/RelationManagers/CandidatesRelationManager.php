<?php

namespace App\Filament\Resources\Elections\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable(),
                FileUpload::make('photo')
                    ->image()
                    ->directory('election-candidates')
                    ->maxSize(3072),
                RichEditor::make('manifesto'),
                RichEditor::make('agenda'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->size(40)
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-user.jpg')),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('User'),
                TextColumn::make('votes_count')
                    ->counts('votes')
                    ->label('Votes')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
                Action::make('import_csv')
                    ->label('Import CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->schema([
                        FileUpload::make('csv_file')
                            ->label('CSV File (columns: name, email, manifesto, agenda, sort_order)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                            ->maxSize(1024)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = Storage::disk('public')->path($data['csv_file']);
                        $rows = array_map('str_getcsv', file($path));

                        $headers = array_map('trim', $rows[0]);
                        unset($rows[0]);

                        $imported = 0;
                        $errors = [];

                        foreach ($rows as $i => $row) {
                            $values = array_map('trim', $row);

                            try {
                                $record = array_combine($headers, $values);
                            } catch (\ValueError) {
                                $errors[] = 'Row '.($i + 1).': column mismatch';

                                continue;
                            }

                            $user = null;
                            if (! empty($record['email'])) {
                                $user = \App\Models\User::where('email', $record['email'])->first();
                            }

                            $this->getOwnerRecord()->candidates()->create([
                                'name' => $record['name'] ?? 'Candidate '.($i + 1),
                                'user_id' => $user?->id,
                                'manifesto' => $record['manifesto'] ?? null,
                                'agenda' => $record['agenda'] ?? null,
                                'sort_order' => (int) ($record['sort_order'] ?? 0),
                            ]);

                            $imported++;
                        }

                        Storage::disk('public')->delete($data['csv_file']);

                        Notification::make()
                            ->title("Imported {$imported} candidate(s)".($errors ? ' with '.count($errors).' error(s)' : ''))
                            ->body($errors ? implode("\n", array_slice($errors, 0, 5)) : null)
                            ->{$errors ? 'warning' : 'success'}()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
