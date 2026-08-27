<div class="rounded-xl border border-gray-200 dark:border-border bg-white dark:bg-card shadow-sm">
    <div class="p-6 md:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Challenges</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $solvedCount }} of {{ $challenges->count() }} solved · {{ $earnedPoints }} / {{ $totalPoints }} points</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ $challenges->count() > 0 ? ($solvedCount / $challenges->count()) * 100 : 0 }}%"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $challenges->count() > 0 ? round(($solvedCount / $challenges->count()) * 100) : 0 }}%</span>
            </div>
        </div>

        @if ($challenges->isEmpty())
            <div class="rounded-lg border border-gray-200 dark:border-border bg-background dark:bg-background/50 px-6 py-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No challenges published yet. Check back later.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($challenges as $challenge)
                    @php
                        $submission = $challenge->submissions->first();
                        $isSolved = $submission?->is_correct;
                    @endphp
                    <div class="rounded-lg border border-gray-100 dark:border-border bg-background dark:bg-background/50 px-5 py-4" x-data="{ open: false }">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    @if ($isSolved)
                                        <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif ($submission && !$isSolved)
                                        <svg class="h-4 w-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @else
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                    <button type="button" @click="open = !open" class="text-left font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $challenge->title }}</button>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1 rounded-md border border-gray-200 dark:border-border px-2 py-0.5 font-medium">{{ $challenge->type }}</span>
                                    <span>{{ $challenge->points }} points</span>
                                    @if ($isSolved)
                                        <span class="text-green-600 dark:text-green-400 font-medium">Solved</span>
                                    @endif
                                </div>
                            </div>
                            @if (!$isSolved && !$submission)
                                <button wire:click="startAnswering({{ $challenge->id }})" class="shrink-0 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition-colors">
                                    Solve
                                </button>
                            @endif
                        </div>

                        <div x-show="open" x-collapse class="mt-3">
                            <p class="text-sm leading-7 text-gray-600 dark:text-gray-400 whitespace-pre-line mb-3">{{ $challenge->description }}</p>

                            @if ($answeringChallengeId === $challenge->id)
                                @if ($showSuccess)
                                    <div class="rounded-lg border border-green-200 dark:border-green-900/30 bg-green-50 dark:bg-green-900/10 px-4 py-3 text-sm text-green-700 dark:text-green-300 mb-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Correct! You earned {{ $challenge->points }} points.</span>
                                        </div>
                                    </div>
                                @else
                                    <form wire:submit="submit" class="space-y-2">
                                        <div>
                                            <input type="text" wire:model="answer" placeholder="Enter your answer..." class="block w-full rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-gray-400">
                                            @error('answer') <span class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition-colors" wire:loading.attr="disabled">
                                                <span wire:loading.remove>Submit</span>
                                                <span wire:loading>Checking...</span>
                                            </button>
                                            <button type="button" wire:click="cancelAnswering" class="rounded-lg border border-gray-300 dark:border-border bg-white dark:bg-gray-700 px-4 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                                        </div>
                                    </form>
                                @endif
                            @endif

                            @if ($submission && !$isSolved)
                                <div class="rounded-lg border border-red-200 dark:border-red-900/30 bg-red-50 dark:bg-red-900/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                                    Incorrect answer submitted. You cannot retry this challenge.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
