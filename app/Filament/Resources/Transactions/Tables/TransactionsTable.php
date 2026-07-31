<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Transaction::query()->with(['creator', 'approver']))
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(40)
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn ($record): string => $record->description),

                TextColumn::make('paid_to_from')
                    ->label('Paid To / From')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => str_replace('_', ' ', ucfirst($state ?? 'unspecified'))),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->sortable(),

                IconColumn::make('requires_approval')
                    ->boolean()
                    ->label('Req. Approval'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('category')
                    ->options([
                        'Membership Dues' => 'Membership Dues',
                        'Donations' => 'Donations',
                        'Sponsorships' => 'Sponsorships',
                        'Fundraising' => 'Fundraising',
                        'Events' => 'Events',
                        'Equipment' => 'Equipment',
                        'Prizes' => 'Prizes',
                        'Refreshments' => 'Refreshments',
                        'Printing' => 'Printing',
                        'Travel' => 'Travel',
                        'Other Income' => 'Other Income',
                        'Other Expense' => 'Other Expense',
                    ])
                    ->searchable(),

                Filter::make('pending_approval')
                    ->label('Pending Approval')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending')->where('requires_approval', true)),

                Filter::make('needs_review')
                    ->label('Needs Review')
                    ->query(fn (Builder $query): Builder => $query->whereNull('approved_by')),

                Filter::make('date_range')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('date_from'),
                        \Filament\Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date))
                            ->when($data['date_to'], fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
