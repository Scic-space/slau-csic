<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CtfDashboardStatsWidget;
use App\Models\CtfCompetition;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CtfDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected string $view = 'filament.pages.ctf-dashboard';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false;
    }

    public function getStats(): array
    {
        return app(CtfDashboardStatsWidget::class)->statistics();
    }

    public function getHeaderWidgets(): array
    {
        return [
            CtfDashboardStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CtfCompetition::query()->withCount('challenges'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'gray' => 'archived',
                    ]),
                TextColumn::make('challenges_count')
                    ->label('Challenges'),
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
