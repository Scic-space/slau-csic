<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $slug = 'testimonials';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('quote')
                    ->rows(3)
                    ->required()
                    ->maxLength(500),
                Toggle::make('is_approved')
                    ->default(false),
                Toggle::make('is_featured')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('user.name')
                    ->label('Submitted by')
                    ->searchable(),
                IconColumn::make('is_approved')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('is_approved')
                    ->query(fn ($query) => $query->where('is_approved', true))
                    ->label('Approved'),
                Filter::make('is_featured')
                    ->query(fn ($query) => $query->where('is_featured', true))
                    ->label('Featured'),
                Filter::make('pending')
                    ->query(fn ($query) => $query->where('is_approved', false))
                    ->label('Pending Review'),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListTestimonials::route('/'),
        ];
    }
}
