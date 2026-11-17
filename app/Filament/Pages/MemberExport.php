<?php

namespace App\Filament\Pages;

use App\Exports\MembersExport;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class MemberExport extends Page
{
    protected string $view = 'filament.pages.member-export';

    protected static ?string $title = 'Export Members';

    protected static ?string $slug = 'member-export';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Export Members';

    protected static string|UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 7;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_users') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'membership_status' => null,
            'membership_type' => null,
            'format' => 'xlsx',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Export Filters')
                    ->description('Choose which members to include and the file format to download.')
                    ->columns(3)
                    ->components([
                        Select::make('membership_status')
                            ->label('Membership Status')
                            ->options([
                                'active' => 'Active',
                                'pending' => 'Pending',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->placeholder('All statuses'),
                        Select::make('membership_type')
                            ->label('Membership Type')
                            ->options([
                                'active' => 'Full Member',
                                'associate' => 'Associate',
                                'alumni' => 'Alumni',
                            ])
                            ->placeholder('All types'),
                        Select::make('format')
                            ->label('File Format')
                            ->options([
                                'xlsx' => 'Excel (.xlsx)',
                                'csv' => 'CSV (.csv)',
                            ])
                            ->default('xlsx')
                            ->required(),
                    ]),
            ]);
    }

    public function download(): mixed
    {
        $state = $this->form->getState();
        $export = (new MembersExport)->setQuery($this->membersQuery($state));
        $filename = 'members-'.now()->format('Y-m-d-His');

        if (($state['format'] ?? 'xlsx') === 'csv') {
            return Excel::download($export, $filename.'.csv', ExcelWriter::CSV);
        }

        return Excel::download($export, $filename.'.xlsx');
    }

    public function getPreviewCount(): int
    {
        return $this->membersQuery($this->form->getState() ?? [])->count();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function membersQuery(array $state): Builder
    {
        return User::query()
            ->when($state['membership_status'] ?? null, fn (Builder $q, string $status) => $q->where('membership_status', $status))
            ->when($state['membership_type'] ?? null, fn (Builder $q, string $type) => $q->where('membership_type', $type))
            ->with(['membership']);
    }
}
