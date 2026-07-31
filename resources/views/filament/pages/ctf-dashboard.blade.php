<x-filament-panels::page>
    {{-- Stats overview --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Competitions</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $this->getStats()['active_competitions'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Solves</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $this->getStats()['total_solves'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Participants</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $this->getStats()['total_participants'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Teams</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $this->getStats()['total_teams'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Pending Writeups</p>
            <p class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-300">{{ $this->getStats()['pending_writeups'] }}</p>
        </div>
    </div>

    {{-- Competitions table --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Competitions</h2>
        </div>
        <div class="p-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
