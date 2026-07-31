<?php

namespace App\Filament\Resources\ExamAttempts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExamAttemptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('total_score')
                    ->numeric()
                    ->disabled(),

                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
