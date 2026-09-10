<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3" data-status-cards="{{ $cardSet }}">
        @foreach ($cards as $card)
            <a
                href="{{ $card['url'] }}"
                wire:navigate
                wire:key="{{ $cardSet }}-status-card-{{ $card['tab'] }}"
                data-tone="{{ $card['tone'] }}"
                @class([
                    'group flex min-h-32 flex-col justify-between rounded-sm border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-400 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-500 dark:bg-card',
                    'border-teal-500 ring-1 ring-teal-500/20 dark:border-teal-400' => $activeTab === $card['tab'],
                    'border-gray-200 dark:border-border' => $activeTab !== $card['tab'],
                ])
                aria-label="Filter by {{ $card['label'] }}"
            >
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $card['label'] }}</p>
                    <span @class([
                        'material-symbols-outlined inline-flex h-10 w-10 items-center justify-center rounded-sm text-[24px]',
                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => in_array($card['tone'], ['slate', 'gray']),
                        'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' => $card['tone'] === 'amber',
                        'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400' => $card['tone'] === 'teal',
                        'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' => $card['tone'] === 'indigo',
                        'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' => $card['tone'] === 'red',
                    ]) aria-hidden="true">{{ $card['icon'] }}</span>
                </div>

                <div class="flex items-end justify-between gap-3 pt-4">
                    <p class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($card['count']) }}</p>
                    <span class="material-symbols-outlined text-[20px] text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-teal-500" aria-hidden="true">arrow_forward</span>
                </div>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
