<x-filament-panels::page>
    @php($stats = $this->getStatistics())

    <x-filament-widgets::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
    />

    <div class="grid gap-6 md:grid-cols-3">
        @foreach ([
            ['label' => 'Active Members', 'value' => $stats['active']],
            ['label' => 'New This Month', 'value' => $stats['new_this_month']],
            ['label' => 'New This Year', 'value' => $stats['new_this_year']],
            ['label' => 'Pending Approval', 'value' => $stats['pending']],
            ['label' => 'Suspended', 'value' => $stats['suspended']],
            ['label' => 'Expiring Soon', 'value' => $stats['expiring_soon']],
        ] as $metric)
            <x-filament::section>
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
                    <span class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ number_format($metric['value']) }}</span>
                </div>
            </x-filament::section>
        @endforeach
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section heading="Active Rate">
            <div class="flex items-end gap-3">
                <span class="text-4xl font-semibold tracking-tight text-primary-600 dark:text-primary-400">{{ $stats['active_rate'] }}%</span>
                <span class="pb-1 text-sm text-gray-500 dark:text-gray-400">of {{ number_format($stats['total']) }} members</span>
            </div>
        </x-filament::section>

        <x-filament::section heading="By Membership Status">
            <dl class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($stats['by_status'] as $label => $count)
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-sm text-gray-600 dark:text-gray-300">{{ $label ?: 'Unspecified' }}</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($count) }}</dd>
                    </div>
                @empty
                    <p class="py-2 text-sm text-gray-500 dark:text-gray-400">No data available.</p>
                @endforelse
            </dl>
        </x-filament::section>

        <x-filament::section heading="By Membership Type">
            <dl class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($stats['by_type'] as $label => $count)
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-sm text-gray-600 dark:text-gray-300">{{ $label ?: 'Unspecified' }}</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($count) }}</dd>
                    </div>
                @empty
                    <p class="py-2 text-sm text-gray-500 dark:text-gray-400">No data available.</p>
                @endforelse
            </dl>
        </x-filament::section>

        <x-filament::section heading="By Faculty">
            <dl class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse ($stats['by_faculty'] as $label => $count)
                    <div class="flex items-center justify-between py-2">
                        <dt class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($count) }}</dd>
                    </div>
                @empty
                    <p class="py-2 text-sm text-gray-500 dark:text-gray-400">No data available.</p>
                @endforelse
            </dl>
        </x-filament::section>
    </div>

    <x-filament-widgets::widgets
        :widgets="$this->getFooterWidgets()"
        :columns="$this->getFooterWidgetsColumns()"
    />
</x-filament-panels::page>
