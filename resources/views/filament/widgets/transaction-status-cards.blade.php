<x-filament-widgets::widget>
    <div class="member-status-grid transaction-status-grid grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3" data-transaction-status-cards>
        @foreach ($cards as $card)
            <a
                href="{{ $this->transactionsUrl($card['tab']) }}"
                wire:navigate
                data-tone="{{ $card['tone'] }}"
                @class([
                    'member-status-card transaction-status-card group flex min-h-28 flex-row items-center justify-between gap-4 rounded-sm border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-400 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-500 dark:bg-gray-900',
                    'border-teal-500 ring-1 ring-teal-500/20 dark:border-teal-400' => $activeTab === $card['tab'],
                    'border-gray-200 dark:border-gray-700' => $activeTab !== $card['tab'],
                ])
                aria-label="Filter transactions by {{ $card['label'] }}"
            >
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $card['label'] }}</p>
                    <p class="mt-2 truncate text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                </div>

                <span @class([
                    'material-symbols-outlined inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-sm text-[24px]',
                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $card['tone'] === 'slate',
                    'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' => $card['tone'] === 'amber',
                    'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400' => $card['tone'] === 'teal',
                    'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' => $card['tone'] === 'red',
                    'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' => $card['tone'] === 'green',
                    'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400' => $card['tone'] === 'purple',
                ]) aria-hidden="true">{{ $card['icon'] }}</span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
