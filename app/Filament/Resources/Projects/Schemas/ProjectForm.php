<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3)
                    ->required(),
                Textarea::make('objectives')
                    ->rows(2),
                Select::make('type')
                    ->options([
                        'research' => 'Research',
                        'development' => 'Development',
                        'ctf' => 'CTF',
                        'competition' => 'Competition',
                        'community' => 'Community',
                        'security_audit' => 'Security Audit',
                    ])
                    ->required(),
                Select::make('status')
                    ->options([
                        'proposed' => 'Proposed',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('proposed')
                    ->required(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                DatePicker::make('actual_completion_date'),
                TextInput::make('repository_url')
                    ->url(),
                TextInput::make('documentation_url')
                    ->url(),
                TextInput::make('progress_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Select::make('lead_id')
                    ->label('Lead')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('tags')
                    ->json()
                    ->placeholder('["laravel", "security", "tool"]'),
            ]);
    }
}
