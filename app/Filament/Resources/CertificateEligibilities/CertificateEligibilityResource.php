<?php

namespace App\Filament\Resources\CertificateEligibilities;

use App\Filament\Resources\CertificateEligibilities\Pages\ListCertificateEligibilities;
use App\Filament\Resources\CertificateEligibilities\Tables\CertificateEligibilitiesTable;
use App\Models\CertificateEligibility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CertificateEligibilityResource extends Resource
{
    protected static ?string $model = CertificateEligibility::class;

    protected static ?string $slug = 'certificates';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?string $navigationLabel = 'Certificates';

    protected static string|UnitEnum|null $navigationGroup = 'Exams';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return CertificateEligibilitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificateEligibilities::route('/'),
        ];
    }
}
