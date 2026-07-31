<?php

namespace App\Filament\Resources\CtfWriteups;

use App\Filament\Resources\CtfWriteups\Pages\ManageCtfWriteups;
use App\Models\CtfWriteup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CtfWriteupResource extends Resource
{
    protected static ?string $model = CtfWriteup::class;

    protected static ?string $slug = 'manage-ctf-writeups';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ctf_challenge_id')
                    ->relationship('challenge', 'title'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Textarea::make('content'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge.competition.title')
                    ->label('Competition')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed By'),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (CtfWriteup $record): bool => $record->status === 'pending')
                    ->action(fn (CtfWriteup $record) => $record->approve(auth()->user())),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (CtfWriteup $record): bool => $record->status === 'pending')
                    ->action(fn (CtfWriteup $record) => $record->reject(auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCtfWriteups::route('/'),
        ];
    }
}
