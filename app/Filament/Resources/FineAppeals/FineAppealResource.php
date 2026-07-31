<?php

namespace App\Filament\Resources\FineAppeals;

use App\Filament\Resources\FineAppeals\Pages\ManageFineAppeals;
use App\Models\FineAppeal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FineAppealResource extends Resource
{
    protected static ?string $model = FineAppeal::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fine_id')
                    ->relationship('fine', 'id')
                    ->required(),
                Select::make('appeal_reason')
                    ->options(FineAppeal::getAppealReasons())
                    ->required(),
                Textarea::make('explanation')
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Textarea::make('decision_notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fine.user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('appeal_reason')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('reviewedBy.name')
                    ->label('Reviewed By'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageFineAppeals::route('/'),
        ];
    }
}
