<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
    />

    <div class="rounded-sm border border-gray-200 bg-white dark:border-border dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Competitions</h2>
        </div>
        <div class="p-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
