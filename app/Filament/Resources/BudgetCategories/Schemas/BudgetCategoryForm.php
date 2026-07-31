<?php

namespace App\Filament\Resources\BudgetCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BudgetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'income' => 'Income',
                                'expense' => 'Expense',
                            ])
                            ->required(),

                        TextInput::make('allocated_amount')
                            ->label('Allocated Amount')
                            ->numeric()
                            ->required()
                            ->prefix('UGX ')
                            ->minValue(0),

                        Select::make('semester')
                            ->options([
                                'Fall' => 'Fall',
                                'Spring' => 'Spring',
                                'Summer' => 'Summer',
                                'Full Year' => 'Full Year',
                            ]),

                        TextInput::make('academic_year')
                            ->label('Academic Year')
                            ->placeholder('e.g. 2025-2026')
                            ->maxLength(20),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
