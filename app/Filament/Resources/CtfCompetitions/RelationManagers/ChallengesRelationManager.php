<?php

namespace App\Filament\Resources\CtfCompetitions\RelationManagers;

use App\Models\CtfChallenge;
use App\Services\CtfService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChallengesRelationManager extends RelationManager
{
    protected static string $relationship = 'challenges';

    protected static ?string $recordTitleAttribute = 'title';

    protected function getDynamicPreview(int $maxPoints, int $minPoints, int $decayFactor): string
    {
        $preview = collect([1, 5, 10, 25, 50, 100])
            ->map(fn ($solves) => sprintf(
                '%d solves → %d pts',
                $solves,
                max($minPoints, intval($maxPoints - ($maxPoints - $minPoints) * (1 - exp(-$solves / $decayFactor))))
            ))
            ->implode(' | ');

        return "Decay curve: {$preview}";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from title'),
                Select::make('ctf_category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Textarea::make('description'),
                TextInput::make('flag')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Leave empty to keep current flag' : '')
                    ->rule(fn (string $operation): string => 'regex:'.CtfChallenge::FLAG_PATTERN),
                Toggle::make('flag_case_sensitive')
                    ->label('Case-Sensitive Flag')
                    ->default(true)
                    ->helperText('When disabled, flags will be compared case-insensitively'),
                TextInput::make('points')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(10000),
                Select::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                        'insane' => 'Insane',
                    ])
                    ->required(),
                Checkbox::make('is_active')
                    ->default(true),
                Repeater::make('hints')
                    ->relationship('hints')
                    ->label('Hint Tiers')
                    ->schema([
                        Textarea::make('content')
                            ->label('Hint Content')
                            ->required(),
                        TextInput::make('cost')
                            ->label('Points Cost')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('tier')
                            ->label('Tier')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->addActionLabel('Add Hint Tier')
                    ->orderable('tier')
                    ->defaultItems(0),
                Select::make('depends_on_challenge_id')
                    ->label('Depends On')
                    ->relationship('dependsOn', 'title', fn ($query) => $query->where('ctf_competition_id', $this->getOwnerRecord()?->id))
                    ->nullable()
                    ->searchable()
                    ->helperText('Challenge must be solved before this one becomes available'),
                TextInput::make('max_attempts')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('0 = unlimited'),
                TextInput::make('tags')
                    ->helperText('Comma-separated tags'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('dynamic_scoring')
                    ->label('Dynamic Scoring')
                    ->default(false)
                    ->live()
                    ->helperText('Points decrease as more people solve the challenge'),
                TextInput::make('min_points')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn ($get) => $get('dynamic_scoring'))
                    ->helperText('Minimum points after decay'),
                TextInput::make('decay_factor')
                    ->numeric()
                    ->default(20)
                    ->minValue(1)
                    ->visible(fn ($get) => $get('dynamic_scoring'))
                    ->helperText(fn ($get) => $this->getDynamicPreview(
                        (int) ($get('points') ?: 500),
                        (int) ($get('min_points') ?: 250),
                        (int) ($get('decay_factor') ?: 20)
                    )),
                Repeater::make('files')
                    ->relationship('files')
                    ->label('Challenge Files')
                    ->schema([
                        FileUpload::make('stored_path')
                            ->label('File')
                            ->disk('public')
                            ->directory('ctf-files')
                            ->acceptedFileTypes([
                                'application/zip',
                                'application/x-tar',
                                'application/gzip',
                                'application/x-bzip2',
                                'application/x-7z-compressed',
                                'application/x-rar-compressed',
                                'text/plain',
                                'text/csv',
                                'application/json',
                                'application/pdf',
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                            ])
                            ->required(),
                        TextInput::make('original_name')
                            ->label('Display Name')
                            ->helperText('Leave blank to use original filename'),
                    ])
                    ->addActionLabel('Add File'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable(),
                TextColumn::make('difficulty')
                    ->badge()
                    ->colors([
                        'success' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                        'gray' => 'insane',
                    ]),
                TextColumn::make('points')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('dynamic_scoring')
                    ->boolean()
                    ->label('Dynamic'),
                TextColumn::make('solve_count')
                    ->label('Solves')
                    ->getStateUsing(fn (CtfChallenge $record): int => $record->getSolveCount()),
                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                        'insane' => 'Insane',
                    ]),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data, self $livewire): CtfChallenge {
                        $competition = $livewire->getOwnerRecord();

                        $data['ctf_competition_id'] = $competition->id;
                        $data['tags'] = ! empty($data['tags'])
                            ? array_map('trim', explode(',', $data['tags']))
                            : null;

                        try {
                            return app(CtfService::class)->createChallenge($data);
                        } catch (\InvalidArgumentException $e) {
                            Log::error('CTF challenge creation failed: '.$e->getMessage());
                            throw $e;
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (CtfChallenge $record): array {
                        return array_merge($record->toArray(), [
                            'tags' => $record->tags ? implode(', ', $record->tags) : '',
                        ]);
                    })
                    ->using(function (CtfChallenge $record, array $data): CtfChallenge {
                        $data['tags'] = ! empty($data['tags'])
                            ? array_map('trim', explode(',', $data['tags']))
                            : null;

                        return app(CtfService::class)->updateChallenge($record, $data);
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}
