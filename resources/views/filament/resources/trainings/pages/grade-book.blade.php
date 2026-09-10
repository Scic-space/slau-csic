<x-filament-panels::page>
    @php
        $training = $this->getRecord();
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-border dark:bg-white/[0.03]">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $training->title }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Manage grades and scores for enrolled students
            </p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
