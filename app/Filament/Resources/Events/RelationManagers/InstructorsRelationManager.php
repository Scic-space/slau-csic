<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstructorsRelationManager extends RelationManager
{
    protected static string $relationship = 'instructors';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role')
                    ->options([
                        'primary' => 'Primary',
                        'co-instructor' => 'Co-Instructor',
                        'guest_speaker' => 'Guest Speaker',
                        'assistant' => 'Assistant',
                    ]),
                Textarea::make('guest_details'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.role')->badge()
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state))),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->options([
                                'primary' => 'Primary',
                                'co-instructor' => 'Co-Instructor',
                                'guest_speaker' => 'Guest Speaker',
                                'assistant' => 'Assistant',
                            ]),
                        Textarea::make('guest_details'),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
