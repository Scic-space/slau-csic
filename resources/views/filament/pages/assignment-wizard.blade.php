<x-filament-panels::page>
    <div
        x-data="{ showSummary: false }"
        @keydown.window.escape.window="$el.querySelector('[x-data=\\'showConfirmApprove\\']') && $el.querySelector('[x-data=\\'showConfirmApprove\\']').__x.$data.showConfirmApprove = false"
    >
        @include('filament.pages.assignment-wizard._header')
        @include('filament.pages.assignment-wizard._step-indicator')

        <div class="mb-6">
            <button
                type="button"
                @click="showSummary = !showSummary"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
                <svg class="h-3.5 w-3.5 transition" :class="showSummary ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                {{ $assignmentId ? 'Edit assignment details' : 'Assignment summary' }}
            </button>
            <div
                x-show="showSummary"
                x-cloak
                x-collapse
                class="mt-3 rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-border dark:bg-gray-900/40"
            >
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $targetType === 'custom' ? ($customName ?: 'Untitled') : ucfirst($targetType) }}</span>
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">
                        {{ count($roles) }} {{ Str::plural('role', count($roles)) }},
                        {{ array_sum(array_column($roles, 'seats')) }} total {{ Str::plural('seat', array_sum(array_column($roles, 'seats'))) }}
                    </span>
                    @if ($priority !== 'medium')
                        <span class="inline-flex items-center gap-1 text-xs {{ $priority === 'high' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
                            {{ ucfirst($priority) }} priority
                        </span>
                    @endif

                </div>
            </div>
        </div>

        <div
            wire:key="step-{{ $step }}"
            class="animate-step-enter"
        >
            @if ($step === 1)
                @include('filament.pages.assignment-wizard._step-target')
            @elseif ($step === 2)
                @include('filament.pages.assignment-wizard._step-roles')
            @elseif ($step === 3)
                @include('filament.pages.assignment-wizard._step-rules')
            @elseif ($step === 4)
                @include('filament.pages.assignment-wizard._step-review')
            @endif
        </div>

        <div class="mt-8">
            @include('filament.pages.assignment-wizard._nav-buttons')
        </div>
    </div>
</x-filament-panels::page>
