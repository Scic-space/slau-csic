<div class="py-4 sm:py-5">
    <div>
        <a href="{{ auth()->user()?->hasRole(['admin', 'super-admin']) ? route('filament.admin.pages.dashboard') : route('dashboard') }}" wire:navigate class="mb-3 inline-flex items-center gap-2 rounded-sm border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-900/20 dark:text-violet-400 dark:hover:bg-violet-900/30 transition-colors shadow-sm">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Dashboard
        </a>
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Fines</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage your club fines</p>
        </div>

        <div class="mb-3 flex flex-wrap justify-end gap-2">
            <a href="{{ route('fines.export.xlsx') }}" class="inline-flex items-center gap-2 rounded-sm bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700"><span class="material-symbols-outlined" aria-hidden="true">table_view</span>XLSX</a>
            <a href="{{ route('account.statement') }}" class="inline-flex items-center gap-2 rounded-sm bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700"><span class="material-symbols-outlined" aria-hidden="true">picture_as_pdf</span>PDF</a>
            <a href="{{ route('fines.export.csv') }}" class="inline-flex items-center gap-2 rounded-sm bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"><span class="material-symbols-outlined" aria-hidden="true">csv</span>CSV</a>
        </div>

        <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Outstanding</p><span class="material-symbols-outlined text-red-500" aria-hidden="true">account_balance_wallet</span></div>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-red-600 dark:text-red-400">UGX {{ number_format($stats['total_outstanding'], 0) }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</p><span class="material-symbols-outlined text-green-500" aria-hidden="true">payments</span></div>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400">UGX {{ number_format($stats['total_paid'], 0) }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue</p><span class="material-symbols-outlined text-orange-500" aria-hidden="true">event_busy</span></div>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['overdue_count'] }}</p>
            </div>
            <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-3"><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Review</p><span class="material-symbols-outlined text-blue-500" aria-hidden="true">pending_actions</span></div>
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['pending_submissions'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($fines->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">You have no fines.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($fines as $fine)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap">{{ $fine->fineType?->name ?? 'General' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $fine->reason }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap">UGX {{ number_format($fine->amount, 0) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap">UGX {{ number_format($fine->amount_paid, 0) }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap font-medium {{ $fine->balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    UGX {{ number_format($fine->balance, 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $fine->status === 'paid' ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $fine->status === 'pending' || $fine->status === 'partially_paid' ? ($fine->is_overdue ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300') : '' }}
                                        {{ $fine->status === 'waived' ? 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                                        @if ($fine->is_overdue)
                                            (Overdue)
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $fine->due_date->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap space-x-2.5">
                                    @if (in_array($fine->status, ['pending', 'partially_paid']))
                                        <button wire:click="openPayment({{ $fine->id }})" class="text-green-600 hover:text-green-500 dark:text-green-400 font-medium">
                                            Pay Now
                                        </button>
                                    @endif
                                    @if ($fine->canBeAppealed())
                                        <button wire:click="openAppeal({{ $fine->id }})" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 font-medium">
                                            Appeal
                                        </button>
                                    @elseif ($fine->hasPendingAppeal())
                                        <span class="text-gray-400 dark:text-gray-500 text-xs">Appeal pending</span>
                                    @endif
                                    <a href="{{ route('fines.notice', $fine->id) }}" target="_blank" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-xs">
                                        Notice
                                    </a>
                                    @if ($fine->payments->isNotEmpty())
                                        @php $lastPayment = $fine->payments->first(); @endphp
                                        @if ($lastPayment->isConfirmed() || $lastPayment->isRecorded())
                                            <a href="{{ route('fines.payments.receipt', $lastPayment->id) }}" target="_blank" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 text-xs">
                                                Receipt
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @if ($fine->payments->isNotEmpty())
                                <tr class="bg-gray-50 dark:bg-gray-900">
                                    <td colspan="8" class="px-6 py-2">
                                        <div class="space-y-1">
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Payments:</span>
                                            @foreach ($fine->payments as $payment)
                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                    <span>UGX {{ number_format($payment->amount, 0) }} on {{ $payment->payment_date->format('M j, Y') }} ({{ $payment->payment_method }})</span>
                                                    <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium
                                                        {{ $payment->isConfirmed() || $payment->isRecorded() ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                        {{ $payment->isPending() ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                                        {{ $payment->isRejected() ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                                        {{ $payment->isRecorded() ? 'Confirmed' : ucfirst($payment->status) }}
                                                    </span>
                                                    @if ($payment->receipt_url)
                                                        <a href="{{ $payment->receipt_url }}" target="_blank" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 underline">Receipt</a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>

        {{-- Pay Now Drawer --}}
        <div x-data="{ open: @entangle('showPaymentForm') }" wire:key="payment-drawer">
            <x-ui.drawer show="open" on-close="$wire.closePayment()" title="Submit Payment">
                <form wire:submit="submitPayment" class="flex min-h-0 flex-1 flex-col">
                    <div class="flex-1 space-y-4 overflow-y-auto p-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (UGX)</label>
                            <input type="number" min="1" wire:model="paymentAmount" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500">
                            @error('paymentAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                            <select wire:model="paymentMethod" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500">
                                @foreach (\App\Models\FinePayment::getPaymentMethods() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('paymentMethod') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt Image (optional)</label>
                            <input type="file" wire:model="paymentReceipt" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-green-700 hover:file:bg-green-100 dark:file:bg-green-900/20 dark:file:text-green-300">
                            @error('paymentReceipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @if ($paymentReceipt)
                                <div class="mt-2">
                                    <img src="{{ $paymentReceipt->temporaryUrl() }}" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-600">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-gray-200 p-5 dark:border-gray-800">
                        <button type="button" wire:click="closePayment" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-500 disabled:opacity-50">
                            <span wire:loading.remove>Submit Payment</span>
                            <span wire:loading>Submitting...</span>
                        </button>
                    </div>
                </form>
            </x-ui.drawer>
        </div>

        {{-- Appeal Drawer --}}
        <div x-data="{ open: @entangle('showAppealForm') }" wire:key="appeal-drawer">
            <x-ui.drawer show="open" on-close="$wire.closeAppeal()" title="Submit Appeal">
                <form wire:submit="submitAppeal" class="flex min-h-0 flex-1 flex-col">
                    <div class="flex-1 space-y-4 overflow-y-auto p-5">
                        <div>
                            <label for="appealReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                            <select wire:model="appealReason" id="appealReason" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a reason</option>
                                @foreach (\App\Models\FineAppeal::getAppealReasons() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('appealReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="appealExplanation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Explanation</label>
                            <textarea wire:model="appealExplanation" id="appealExplanation" rows="4" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Explain why this fine should be waived..."></textarea>
                            @error('appealExplanation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-gray-200 p-5 dark:border-gray-800">
                        <button type="button" wire:click="closeAppeal" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                            Submit Appeal
                        </button>
                    </div>
                </form>
            </x-ui.drawer>
        </div>
    </div>
</div>
