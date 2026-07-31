<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\BudgetCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('type')
                            ->options([
                                'income' => 'Income',
                                'expense' => 'Expense',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('UGX ')
                            ->minValue(0.01),

                        DatePicker::make('date')
                            ->required()
                            ->default(now()),

                        Select::make('category')
                            ->options(fn ($get) => $get('type') === 'expense'
                                ? array_combine(BudgetCategory::getExpenseCategories(), BudgetCategory::getExpenseCategories())
                                : array_combine(BudgetCategory::getIncomeCategories(), BudgetCategory::getIncomeCategories()))
                            ->required()
                            ->searchable(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'check' => 'Check',
                                'card' => 'Card',
                                'transfer' => 'Bank Transfer',
                                'other' => 'Other',
                            ]),

                        TextInput::make('paid_to_from')
                            ->label('Paid To / Received From')
                            ->maxLength(255),

                        Toggle::make('requires_approval')
                            ->label('Requires Approval')
                            ->default(true),

                        Select::make('created_by')
                            ->relationship('creator', 'name')
                            ->default(auth()->id())
                            ->required(),
                    ]),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('receipt_path')
                    ->label('Receipt')
                    ->directory('receipts')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/*', 'application/pdf']),
            ]);
    }
}
