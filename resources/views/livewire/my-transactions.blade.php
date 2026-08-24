<div class="py-4 sm:py-5">
    <div>
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Transactions</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View your financial transactions and payment history</p>
        </div>

        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-2 sm:flex-row">
                <select wire:model.live="typeFilter" aria-label="Filter by transaction type" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:w-40 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Types</option><option value="income">Income</option><option value="expense">Expense</option>
                </select>
                <select wire:model.live="statusFilter" aria-label="Filter by transaction status" class="block w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:w-40 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <a href="{{ route('transactions.export.xlsx') }}" class="inline-flex items-center gap-2 rounded-sm bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700"><span class="material-symbols-outlined" aria-hidden="true">table_view</span>XLSX</a>
                <a href="{{ route('account.statement') }}" class="inline-flex items-center gap-2 rounded-sm bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700"><span class="material-symbols-outlined" aria-hidden="true">picture_as_pdf</span>PDF</a>
                <a href="{{ route('transactions.export.csv') }}" class="inline-flex items-center gap-2 rounded-sm bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"><span class="material-symbols-outlined" aria-hidden="true">csv</span>CSV</a>
            </div>
        </div>

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</p><span class="material-symbols-outlined text-green-500" aria-hidden="true">trending_up</span></div>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">UGX {{ number_format($stats['total_income'], 0) }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p><span class="material-symbols-outlined text-red-500" aria-hidden="true">trending_down</span></div>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">UGX {{ number_format($stats['total_expenses'], 0) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($transactions->isEmpty())
                <div class="p-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">No transactions found</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your financial transactions will appear here.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($transactions as $t)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $t['description'] }}</p>
                                    @if ($t['category'])
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t['category'] }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $t['type'] === 'income' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $t['type'] === 'expense' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                        {{ ucfirst($t['type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                    {{ $t['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $t['type'] === 'income' ? '+' : '-' }}UGX {{ number_format($t['amount'], 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $t['status'] === 'approved' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $t['status'] === 'pending' ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                        {{ $t['status'] === 'rejected' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                        {{ ucfirst($t['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $t['created_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
