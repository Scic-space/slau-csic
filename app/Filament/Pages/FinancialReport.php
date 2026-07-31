<?php

namespace App\Filament\Pages;

use App\Exports\FinancialReportExport;
use App\Models\Transaction;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class FinancialReport extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.financial-report';

    public string $dateRange = 'this_month';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $reportType = 'summary';

    protected $queryString = [
        'dateRange' => ['except' => 'this_month'],
        'reportType' => ['except' => 'summary'],
    ];

    protected function rules(): array
    {
        return [
            'dateRange' => ['required', 'in:this_month,last_month,this_quarter,this_year,custom'],
            'reportType' => ['required', 'in:summary,detailed'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ];
    }

    public function mount(): void
    {
        $this->setDateRange();
    }

    public function setDateRange(): void
    {
        $this->startDate = match ($this->dateRange) {
            'this_month' => now()->startOfMonth()->format('Y-m-d'),
            'last_month' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'this_quarter' => now()->startOfQuarter()->format('Y-m-d'),
            'this_year' => now()->startOfYear()->format('Y-m-d'),
            default => now()->startOfMonth()->format('Y-m-d'),
        };

        if ($this->dateRange !== 'custom') {
            $this->endDate = match ($this->dateRange) {
                'this_month' => now()->endOfMonth()->format('Y-m-d'),
                'last_month' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
                'this_quarter' => now()->endOfQuarter()->format('Y-m-d'),
                'this_year' => now()->endOfYear()->format('Y-m-d'),
                default => now()->endOfMonth()->format('Y-m-d'),
            };
        }
    }

    public function updatedDateRange(): void
    {
        $this->setDateRange();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTransactionsQuery())
            ->columns([
                TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('Date'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record): string => $record->category),

                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(fn ($record): string => $record->description ?? 'No description'),

                TextColumn::make('amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->limit(20)
                    ->tooltip(fn ($record): string => $record->creator?->name ?? 'System'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ])
                    ->label('Transaction Type'),

                SelectFilter::make('category')
                    ->options(fn () => Transaction::query()
                        ->whereBetween('date', [$this->startDate, $this->endDate])
                        ->distinct('category')
                        ->pluck('category')
                        ->sort()
                        ->mapWithKeys(fn ($category) => [$category => $category])
                    )
                    ->searchable()
                    ->label('Category'),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(fn () => $this->exportToPDF()),

                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(fn () => $this->exportToExcel()),
            ])
            ->striped()
            ->deferLoading();
    }

    protected function getTransactionsQuery(): Builder
    {
        return Transaction::query()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->where('status', 'approved')
            ->with(['creator']);
    }

    public function getReportData(): array
    {
        $query = $this->getTransactionsQuery();

        $totalIncome = (float) $query->clone()->where('type', 'income')->sum('amount');
        $totalExpenses = (float) $query->clone()->where('type', 'expense')->sum('amount');
        $netIncome = $totalIncome - $totalExpenses;

        $incomeByCategory = $query->clone()
            ->where('type', 'income')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $expensesByCategory = $query->clone()
            ->where('type', 'expense')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $driver = DB::connection()->getDriverName();

        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', date)" : 'YEAR(date)';
        $monthExpr = $driver === 'sqlite' ? "strftime('%m', date)" : 'MONTH(date)';

        $monthlyTrend = Transaction::select(
            DB::raw("{$yearExpr} as year"),
            DB::raw("{$monthExpr} as month"),
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses")
        )
            ->where('status', 'approved')
            ->whereDate('date', '>=', now()->subMonths(12))
            ->groupBy(DB::raw("{$yearExpr}, {$monthExpr}"))
            ->orderBy(DB::raw("{$yearExpr}"))
            ->orderBy(DB::raw("{$monthExpr}"))
            ->get();

        return [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $netIncome,
            'incomeByCategory' => $incomeByCategory,
            'expensesByCategory' => $expensesByCategory,
            'monthlyTrend' => $monthlyTrend,
            'transactionCount' => (int) $query->count(),
            'averageTransaction' => (float) $query->avg('amount'),
        ];
    }

    public function exportToPDF(): mixed
    {
        try {
            $data = $this->cleanDataForPdf($this->getReportData());

            $pdf = Pdf::loadView('livewire.admin.reports.pdf-statement', [
                'data' => $data,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'reportType' => $this->reportType,
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'financial-statement-'.now()->format('Y-m-d').'.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Export Error: '.$e->getMessage());

            Notification::make()
                ->title('PDF export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }

    public function exportToExcel(): mixed
    {
        try {
            $cleanData = $this->cleanExportData($this->getReportData());

            return Excel::download(
                new FinancialReportExport($cleanData, $this->startDate, $this->endDate, $this->reportType),
                'financial-statement-'.now()->format('Y-m-d').'.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('Excel Export Error: '.$e->getMessage());
            session()->flash('error', 'Failed to generate Excel file: '.$e->getMessage());

            return null;
        }
    }

    private function cleanDataForPdf(mixed $data): mixed
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(fn ($item): mixed => $this->cleanDataForPdf($item));
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->cleanDataForPdf($value);
            }

            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanDataForPdf($value);
            }

            return $data;
        }

        if (is_string($data)) {
            return $this->cleanString($data);
        }

        return $data;
    }

    private function cleanExportData(mixed $data): mixed
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        array_walk_recursive($data, function (&$value): void {
            if (is_string($value)) {
                $value = strip_tags($this->cleanString($value));
            }
        });

        return $data;
    }

    private function cleanString(string $string): string
    {
        if ($string === '') {
            return $string;
        }

        if (! mb_check_encoding($string, 'UTF-8')) {
            $encoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'ASCII', 'Windows-1252'], true);

            if ($encoding && $encoding !== 'UTF-8') {
                $string = mb_convert_encoding($string, 'UTF-8', $encoding);
            } else {
                $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            }
        }

        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
        $string = preg_replace('/[\xC0-\xC1][\x80-\xBF]/', '', $string);
        $string = preg_replace('/[\xC0-\xFF][\x80-\xBF]{0,2}$/', '', $string);
        $string = preg_replace('/[\xE0-\xEF][\x80-\xBF]{0,1}[\xC0-\xFF]/', '', $string);

        return trim($string);
    }
}
