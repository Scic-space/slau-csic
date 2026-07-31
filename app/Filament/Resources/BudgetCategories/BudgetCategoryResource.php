<?php

namespace App\Filament\Resources\BudgetCategories;

use App\Filament\Resources\BudgetCategories\Pages\CreateBudgetCategory;
use App\Filament\Resources\BudgetCategories\Pages\EditBudgetCategory;
use App\Filament\Resources\BudgetCategories\Pages\ListBudgetCategories;
use App\Filament\Resources\BudgetCategories\Schemas\BudgetCategoryForm;
use App\Filament\Resources\BudgetCategories\Tables\BudgetCategoriesTable;
use App\Models\BudgetCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BudgetCategoryResource extends Resource
{
    protected static ?string $model = BudgetCategory::class;

    protected static ?string $slug = 'budget-categories';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Budget Categories';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BudgetCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetCategories::route('/'),
            'create' => CreateBudgetCategory::route('/create'),
            'edit' => EditBudgetCategory::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }
}
