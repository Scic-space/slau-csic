<?php

namespace App\Filament\Resources\RoleTemplates;

use App\Filament\Resources\RoleTemplates\Pages\ManageRoleTemplates;
use App\Models\RoleTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleTemplateResource extends Resource
{
    protected static ?string $model = RoleTemplate::class;

    protected static ?string $slug = 'role-templates';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->options([
                        'technical' => 'Technical',
                        'leadership' => 'Leadership',
                        'operations' => 'Operations',
                        'general' => 'General',
                    ])
                    ->default('general')
                    ->required(),
                CheckboxList::make('required_skills')
                    ->options([
                        'PHP' => 'PHP',
                        'JavaScript' => 'JavaScript',
                        'Python' => 'Python',
                        'Design' => 'Design',
                        'Writing' => 'Writing',
                        'Leadership' => 'Leadership',
                        'Communication' => 'Communication',
                        'Data Analysis' => 'Data Analysis',
                        'Project Management' => 'Project Management',
                        'Development' => 'Development',
                        'Algorithms' => 'Algorithms',
                        'Web' => 'Web',
                        'UI/UX' => 'UI/UX',
                    ])
                    ->columns(3),
                Select::make('min_experience')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->nullable(),
                Select::make('availability_requirement')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->nullable(),
                Select::make('approval_route')
                    ->options([
                        'auto' => 'Auto-approve',
                        'admin' => 'Admin approval',
                        'lead' => 'Team lead approval',
                    ])
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'technical' => 'info',
                        'leadership' => 'warning',
                        'operations' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('required_skills')
                    ->badge()
                    ->separator(','),
                TextColumn::make('min_experience')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'advanced' => 'danger',
                        'intermediate' => 'warning',
                        'beginner' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
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
            'index' => ManageRoleTemplates::route('/'),
        ];
    }
}
