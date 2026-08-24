<x-filament-widgets::widget>
    <div class="member-status-grid grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3" data-member-status-cards>
        @foreach ($cards as $card)
            <a
                href="{{ $this->usersUrl($card['tab']) }}"
                wire:navigate
                data-tone="{{ $card['tone'] }}"
                @class([
                    'member-status-card',
                    'is-active' => $activeTab === $card['tab'],
                    'group flex min-h-32 flex-col justify-between rounded-sm border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-400 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-500 dark:bg-gray-900',
                    'border-teal-500 ring-1 ring-teal-500/20 dark:border-teal-400' => $activeTab === $card['tab'],
                    'border-gray-200 dark:border-gray-700' => $activeTab !== $card['tab'],
                ])
                aria-label="Filter members by {{ $card['label'] }}"
            >
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $card['label'] }}</p>
                    <span @class([
                        'member-status-card-icon',
                        'material-symbols-outlined inline-flex h-10 w-10 items-center justify-center rounded-sm text-[24px]',
                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $card['tone'] === 'slate',
                        'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' => $card['tone'] === 'amber',
                        'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-400' => $card['tone'] === 'teal',
                        'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' => $card['tone'] === 'indigo',
                        'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' => $card['tone'] === 'red',
                        'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400' => $card['tone'] === 'orange',
                    ]) aria-hidden="true">{{ $card['icon'] }}</span>
                </div>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <p class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($card['count']) }}</p>
                    <span class="material-symbols-outlined text-[20px] text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-teal-500" aria-hidden="true">arrow_forward</span>
                </div>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
