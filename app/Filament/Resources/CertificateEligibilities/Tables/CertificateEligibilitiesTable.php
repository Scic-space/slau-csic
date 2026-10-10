<?php

namespace App\Filament\Resources\CertificateEligibilities\Tables;

use App\Models\CertificateEligibility;
use App\Models\Exam;
use App\Services\CertificateService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CertificateEligibilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('exam.title')
                    ->label('Exam')
                    ->searchable(),

                TextColumn::make('eligible')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Eligible' : 'Revoked'),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Awarded')
                    ->dateTime('M j, Y'),
            ])
            ->filters([
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->options(fn (): array => Exam::whereHas('certificateEligibilities')->orderBy('title')->pluck('title', 'id')->toArray()),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (CertificateEligibility $record): bool => $record->eligible)
                    ->action(fn (CertificateEligibility $record) => app(CertificateService::class)->downloadPdf($record)),

                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (CertificateEligibility $record): bool => $record->eligible)
                    ->action(function (CertificateEligibility $record): void {
                        app(CertificateService::class)->revokeEligibility($record);

                        Notification::make()
                            ->title('Certificate revoked')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
