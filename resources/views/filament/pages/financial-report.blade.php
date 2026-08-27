<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header with Date Range Selection -->
        <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Financial Reports</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Generate and export financial statements</p>
                </div>

                <!-- Date Range Controls -->
                <div class="flex items-center gap-4">
                    <select
                        wire:model.live="dateRange"
                        class="rounded-md border-gray-300 dark:border-border dark:bg-gray-700 dark:text-white"
                    >
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>

                    @if($dateRange === 'custom')
                        <div class="flex items-center gap-2">
                            <input
                                type="date"
                                wire:model.live="startDate"
                                class="rounded-md border-gray-300 dark:border-border dark:bg-gray-700 dark:text-white"
                            />
                            <span class="text-gray-500">to</span>
                            <input
                                type="date"
                                wire:model.live="endDate"
                                class="rounded-md border-gray-300 dark:border-border dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @php $reportData = $this->getReportData(); @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Income</p>
                        <p class="text-2xl font-bold text-green-600">
                            UGX {{ number_format($reportData['totalIncome'], 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9m-4 0V5a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Expenses</p>
                        <p class="text-2xl font-bold text-red-600">
                            UGX {{ number_format($reportData['totalExpenses'], 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 {{ $reportData['netIncome'] >= 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-orange-100 dark:bg-orange-900' }} rounded-lg">
                        <svg class="w-6 h-6 {{ $reportData['netIncome'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M15 7h6m0 10v-3m-3 3h.01M9 17h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Income</p>
                        <p class="text-2xl font-bold {{ $reportData['netIncome'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                            UGX {{ number_format($reportData['netIncome'], 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9m-4 0V5a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Transactions</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $reportData['transactionCount'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Income Breakdown -->
            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income Breakdown</h3>
                <div class="space-y-3">
                    @foreach($reportData['incomeByCategory'] as $income)
                        <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div>
                                <p class="font-medium text-green-800 dark:text-green-200">{{ $income->category }}</p>
                                <p class="text-sm text-green-600 dark:text-green-400">{{ $income->count }} transaction{{ $income->count == 1 ? '' : 's' }}</p>
                            </div>
                            <p class="text-lg font-bold text-green-800 dark:text-green-200">
                                UGX {{ number_format($income->total, 0) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense Breakdown</h3>
                <div class="space-y-3">
                    @foreach($reportData['expensesByCategory'] as $expense)
                        <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div>
                                <p class="font-medium text-red-800 dark:text-red-200">{{ $expense->category }}</p>
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $expense->count }} transaction{{ $expense->count == 1 ? '' : 's' }}</p>
                            </div>
                            <p class="text-lg font-bold text-red-800 dark:text-red-200">
                                UGX {{ number_format($expense->total, 0) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-card rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transaction Details</h3>
                <div class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                </div>
            </div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
