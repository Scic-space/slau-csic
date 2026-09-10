<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="treasurerData()">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">TreasurerDashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Financial overview and pending payment approvals</p>
        </div>

        {{-- JSON data for charts and test assertions --}}
        <script>
            window.treasurerData = {
                totalIncome: {{ $stats['total_income'] }},
                totalExpenses: {{ $stats['total_expenses'] }},
                budgetData: {!! $budgetCategories->toJson() !!},
                recentTransactions: {!! $recentTransactions->toJson() !!},
                pendingApprovals: {{ $stats['pending_payments_count'] }},
                spendingTrend: { labels: {{ $chartLabels->toJson() }}, income: {{ $chartIncome->toJson() }}, expenses: {{ $chartExpenses->toJson() }} },
            };
            function treasurerData() {
                return { data: window.treasurerData };
            }
        </script>

        {{-- Stats Grid --}}
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400" x-text="'UGX ' + window.treasurerData.totalIncome.toLocaleString()">UGX {{ number_format($stats['total_income'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
                <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400" x-text="'UGX ' + window.treasurerData.totalExpenses.toLocaleString()">UGX {{ number_format($stats['total_expenses'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Balance</p>
                <p class="mt-2 text-2xl font-bold {{ $stats['net_balance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    UGX {{ number_format($stats['net_balance'], 0) }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Payments</p>
                <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending_payments_count'] }}</p>
            </div>
        </div>

        {{-- Fines Stats Row --}}
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fines Issued</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">UGX {{ number_format($stats['total_fines_issued'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fines Collected</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">UGX {{ number_format($stats['total_fines_collected'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue Fines</p>
                <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue_fines_count'] }}</p>
            </div>
        </div>

        {{-- Budget Monitoring --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Budget Overview</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($budgetCategories as $cat)
                    @php
                        $percent = $cat['allocated'] > 0 ? min(100, round(($cat['spent'] / $cat['allocated']) * 100)) : 0;
                        $barColor = $percent >= 100 ? 'bg-red-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-green-500');
                    @endphp
                    <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $cat['name'] }}</p>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ ucfirst($cat['type']) }}</span>
                        </div>
                        <div class="flex items-baseline justify-between mb-2">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">UGX {{ number_format($cat['spent'], 0) }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">of UGX {{ number_format($cat['allocated'], 0) }}</span>
                        </div>
                        <div class="w-full rounded-full bg-gray-200 dark:bg-gray-700 h-2.5">
                            <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-right text-gray-500 dark:text-gray-400">{{ $percent }}% used</p>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card p-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No active budget categories.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Income vs Expenses Chart --}}
        <div class="mb-8 rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Income vs Expenses (6 Months)</h2>
            <div id="treasurerChart" x-data="{}" x-init="
                const options = {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [
                        { name: 'Income', data: {{ $chartIncome->toJson() }} },
                        { name: 'Expenses', data: {{ $chartExpenses->toJson() }} }
                    ],
                    xaxis: { categories: {{ $chartLabels->toJson() }}, labels: { style: { colors: '#9ca3af', fontSize: '11px' } } },
                    yaxis: { labels: { formatter: v => 'UGX ' + v.toLocaleString(), style: { colors: '#9ca3af', fontSize: '11px' } } },
                    colors: ['#16a34a', '#dc2626'],
                    plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    legend: { position: 'top', labels: { colors: '#9ca3af' } },
                    grid: { borderColor: '#374151', strokeDashArray: 3 },
                };
                new ApexCharts($el, options).render();
            "></div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            {{-- Pending Payments --}}
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Payment Approvals</h2>
                </div>
                @if ($pendingPayments->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No pending payments.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($pendingPayments as $p)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $p['member'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            UGX {{ number_format($p['amount'], 0) }} via {{ ucfirst($p['method']) }}
                                        </p>
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $p['type'] }}</span>
                                            <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">{{ $p['submitted_at'] }}</span>
                                        </div>
                                        @if ($p['has_receipt'])
                                            <a href="{{ $p['receipt_url'] }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-500 dark:text-blue-400">View Receipt</a>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <button wire:click="confirmPayment({{ $p['id'] }})" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-500 focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                                            Confirm
                                        </button>
                                        <button wire:click="rejectPayment({{ $p['id'] }})" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:border-border dark:bg-card dark:text-gray-300 dark:hover:bg-card-hover">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-border">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Transactions</h2>
                </div>
                @if ($recentTransactions->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No transactions yet.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentTransactions as $t)
                            <div class="px-5 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $t['description'] }}</p>
                                        <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $t['category'] }}</span>
                                            <span>&middot;</span>
                                            <span>{{ $t['created_at'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-4">
                                        <p class="text-sm font-semibold {{ $t['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $t['type'] === 'income' ? '+' : '-' }}UGX {{ number_format($t['amount'], 0) }}
                                        </p>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                            {{ $t['status'] === 'approved' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $t['status'] === 'pending' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                            {{ $t['status'] === 'rejected' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                            {{ ucfirst($t['status']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
