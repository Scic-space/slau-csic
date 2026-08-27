<div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm">
    <div class="p-6 md:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Track your progress</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Keep your work up to date so your dashboard reflects your real progress.</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                    @if ($status === 'completed') bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-500/20
                    @elseif ($status === 'in_progress') bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-500/20
                    @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 border border-gray-500/20 @endif">
                    <span class="mr-1 h-1.5 w-1.5 rounded-full
                        @if ($status === 'completed') bg-green-500
                        @elseif ($status === 'in_progress') bg-blue-500
                        @else bg-gray-400 @endif"></span>
                    {{ str_replace('_', ' ', ucfirst($status)) }}
                </span>

                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $progressPercentage }}%</span>
            </div>
        </div>

        <div class="mb-6">
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full rounded-full transition-all duration-500 ease-out
                    @if ($progressPercentage === 100) bg-green-500
                    @elseif ($progressPercentage > 0) bg-indigo-500
                    @else bg-gray-300 dark:bg-gray-600 @endif"
                    style="width: {{ $progressPercentage }}%">
                </div>
            </div>
            <div class="mt-1 flex justify-between text-xs text-gray-400 dark:text-gray-500">
                <span>0%</span>
                <span>{{ $completedUnits }}/{{ $resource->target_total }} units</span>
                <span>100%</span>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-gray-100 dark:border-border bg-background dark:bg-background/50 px-3 py-3 text-center">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $progressPercentage }}%</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Progress</p>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-border bg-background dark:bg-background/50 px-3 py-3 text-center">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $completedUnits }} / {{ $resource->target_total }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Units</p>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-border bg-background dark:bg-background/50 px-3 py-3 text-center">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $score }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Score</p>
            </div>
            <div class="rounded-lg border border-gray-100 dark:border-border bg-background dark:bg-background/50 px-3 py-3 text-center">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ranking ?: '—' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ranking</p>
            </div>
        </div>

        @if (!$this->canAwardPoints())
            <div class="mb-6 rounded-lg border border-amber-200 dark:border-amber-900/30 bg-amber-50 dark:bg-amber-900/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Score and ranking are awarded by club staff after review. Track your effort below — points will be updated once your work is assessed.</span>
                </div>
            </div>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select id="status" wire:model="status" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="not_started">Not started</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                    </select>
                    @error('status') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="progressPercentage" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Progress %</label>
                    <div class="relative">
                        <input id="progressPercentage" type="range" wire:model.live="progressPercentage" min="0" max="100" class="w-full accent-indigo-500">
                        <span class="absolute -top-7 right-0 text-xs font-medium text-gray-600 dark:text-gray-400">{{ $progressPercentage }}%</span>
                    </div>
                    @error('progressPercentage') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="completedUnits" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Completed units</label>
                    <input id="completedUnits" type="number" wire:model="completedUnits" min="0" max="{{ $resource->target_total }}" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('completedUnits') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="score" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Score
                        @if (!$this->canAwardPoints()) <span class="font-normal text-gray-400 dark:text-gray-500">(staff-set)</span> @endif
                    </label>
                    @if ($this->canAwardPoints())
                        <input id="score" type="number" wire:model="score" min="0" class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('score') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    @else
                        <div class="block w-full rounded-lg border border-gray-200 dark:border-border bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $score }}</div>
                    @endif
                </div>

                <div>
                    <label for="ranking" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Ranking
                        @if (!$this->canAwardPoints()) <span class="font-normal text-gray-400 dark:text-gray-500">(staff-set)</span> @endif
                    </label>
                    @if ($this->canAwardPoints())
                        <input id="ranking" type="text" wire:model="ranking" placeholder="Top 10, quarter finalist..." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-gray-400">
                        @error('ranking') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    @else
                        <div class="block w-full rounded-lg border border-gray-200 dark:border-border bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $ranking ?: 'Not set' }}</div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" wire:model="notes" rows="3" placeholder="What you solved, what blocked you, or what comes next." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2.5 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-gray-400"></textarea>
                    @error('notes') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div>
                    @if ($showSaved)
                        <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400" x-data x-init="setTimeout(() => $wire.dismissSaved(), 3000)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Progress saved
                        </div>
                    @endif
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-emerald-500 dark:text-slate-950 dark:hover:bg-emerald-400 disabled:opacity-50" wire:loading.attr="disabled" wire:target="save">
                    <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    <span wire:loading.remove wire:target="save">Save progress</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
