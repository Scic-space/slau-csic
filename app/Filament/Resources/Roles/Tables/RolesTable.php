<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Support\AdminActionStyle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Role::query()->withCount('users')->withCount('permissions'))
            ->columns([
                TextColumn::make('name')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super-admin' => 'danger',
                        'admin' => 'warning',
                        'President' => 'primary',
                        'Treasurer' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state): string => str_replace('-', ' ', ucwords($state)))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable(),

                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                AdminActionStyle::apply(ViewAction::make(), 'View', 'visibility', 'info'),
                AdminActionStyle::apply(EditAction::make(), 'Edit', 'edit', 'teal'),
                AdminActionStyle::apply(DeleteAction::make(), 'Delete', 'delete', 'danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
