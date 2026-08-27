<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Cache Management --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Cache Management</h2>
            </div>
            <div class="flex flex-col gap-3 p-6">
                {{ $this->clearCacheAction }}
                {{ $this->clearConfigAction }}
                {{ $this->clearViewAction }}
                {{ $this->clearRouteAction }}
                {{ $this->optimizeAction }}
            </div>
        </div>

        {{-- System Health --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">System Health</h2>
            </div>
            <div class="p-6">
                <dl class="space-y-3">
                    @foreach ($this->getSystemInfo() as $label => $value)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                @if ($value === true || $value === 'Enabled')
                                    <span class="text-success-600 dark:text-success-400">{{ $value }}</span>
                                @elseif ($value === false || $value === 'Disabled')
                                    <span class="text-danger-600 dark:text-danger-400">{{ $value }}</span>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Database Backup --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-border dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-border">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Database Backup</h2>
            </div>
            <div class="p-6">
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Download a backup of the current database.
                </p>
                {{ $this->downloadBackupAction }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
