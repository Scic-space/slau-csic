<?php

namespace App\Filament\Resources\QuestionBankQuestions;

use App\Filament\Resources\QuestionBankQuestions\Pages\CreateQuestionBankQuestion;
use App\Filament\Resources\QuestionBankQuestions\Pages\EditQuestionBankQuestion;
use App\Filament\Resources\QuestionBankQuestions\Pages\ListQuestionBankQuestions;
use App\Filament\Resources\QuestionBankQuestions\Schemas\QuestionBankQuestionForm;
use App\Filament\Resources\QuestionBankQuestions\Tables\QuestionBankQuestionsTable;
use App\Models\QuestionBankQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionBankQuestionResource extends Resource
{
    protected static ?string $model = QuestionBankQuestion::class;

    protected static ?string $slug = 'questions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Question Bank';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return QuestionBankQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionBankQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionBankQuestions::route('/'),
            'create' => CreateQuestionBankQuestion::route('/create'),
            'edit' => EditQuestionBankQuestion::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
