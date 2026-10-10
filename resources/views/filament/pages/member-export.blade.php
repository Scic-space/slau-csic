<x-filament-panels::page>
    <form wire:submit="download" class="flex flex-col gap-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4">
            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray">
                Download Export
            </x-filament::button>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ number_format($this->getPreviewCount()) }} member(s) match the current filters.
            </span>
        </div>
    </form>
</x-filament-panels::page>
