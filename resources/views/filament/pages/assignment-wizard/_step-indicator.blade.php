<div class="mb-10">
    <nav class="relative grid grid-cols-4">
        <div class="absolute left-[12.5%] right-[12.5%] top-6 -z-0 h-0.5 bg-gray-200 dark:bg-gray-700">
            <div class="h-0.5 bg-primary-500 transition-all duration-500 ease-out" style="width: {{ ($step - 1) * 33.33 }}%"></div>
        </div>
        @foreach ([
            ['num' => 1, 'label' => 'Target', 'desc' => 'Define scope'],
            ['num' => 2, 'label' => 'Roles', 'desc' => 'Add positions'],
            ['num' => 3, 'label' => 'Rules', 'desc' => 'Set weights'],
            ['num' => 4, 'label' => 'Review', 'desc' => 'Approve'],
        ] as $s)
            @php
                $isPast = $step > $s['num'];
                $isCurrent = $step === $s['num'];
                $isFuture = $step < $s['num'];
            @endphp
            <div class="flex justify-center">
                <button
                    wire:click="goToStep({{ $s['num'] }})"
                    @if ($isFuture) disabled @endif
                    class="relative flex flex-col items-center {{ $isFuture ? 'cursor-not-allowed' : 'cursor-pointer' }} group"
                >
                    <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold transition-all duration-300
                        {{ $isCurrent
                            ? 'border-primary-500 bg-primary-50 text-primary-700 shadow-sm dark:border-primary-400 dark:bg-primary-900/30 dark:text-primary-300'
                            : ($isPast
                                ? 'border-green-500 bg-green-50 text-green-700 dark:border-green-400 dark:bg-green-900/30 dark:text-green-300'
                                : 'border-gray-200 bg-white text-gray-300 dark:border-border dark:bg-card dark:text-gray-600') }}
                    ">
                        @if ($isPast)
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span>{{ $s['num'] }}</span>
                        @endif
                    </span>
                    <span class="mt-2 text-xs font-bold {{ $isCurrent ? 'text-primary-700 dark:text-primary-300' : ($isPast ? 'text-green-700 dark:text-green-300' : 'text-gray-300 dark:text-gray-600') }}">
                        {{ $s['label'] }}
                    </span>
                    <span class="text-[10px] leading-tight mt-0.5 {{ $isCurrent ? 'text-primary-500 dark:text-primary-400' : ($isPast ? 'text-green-500 dark:text-green-400' : 'text-gray-300 dark:text-gray-600') }}">
                        {{ $s['desc'] }}
                    </span>
                </button>
            </div>
        @endforeach
    </nav>

    <div class="mt-6 flex items-center justify-between rounded-xl border border-gray-200 bg-white px-5 py-3 shadow-sm dark:border-border dark:bg-card">
        <div class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 text-xs font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">
                {{ $step }}
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    Step {{ $step }}: <span class="text-primary-700 dark:text-primary-300">{{ $stepLabels[$step] }}</span>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stepDescriptions[$step] }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-2 w-20 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-full rounded-full bg-primary-500 transition-all duration-500" style="width: {{ ($step / 4) * 100 }}%"></div>
            </div>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ round(($step / 4) * 100) }}%</span>
        </div>
    </div>
</div>
