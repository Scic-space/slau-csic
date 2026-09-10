<div class="flex items-center justify-between" x-data="{ showConfirmApprove: false }">
    <div>
        @if ($step > 1)
            <button type="button" wire:click="prevStep" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-border dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </button>
        @endif
    </div>

    <div class="flex items-center gap-3">
        @if ($step < 3)
            <button type="button" wire:click="nextStep" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-500">
                Continue
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @elseif ($step === 3)
            <button
                type="button"
                wire:click="generateAndGoToReview"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-500 disabled:opacity-50"
            >
                <svg wire:loading.remove wire:target="generateAndGoToReview" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <svg wire:loading wire:target="generateAndGoToReview" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                <span wire:loading.remove wire:target="generateAndGoToReview">Create &amp; Auto-Assign</span>
                <span wire:loading wire:target="generateAndGoToReview">Generating...</span>
            </button>
        @elseif ($step === 4)
            @if ($generatedResults && ($generatedResults['status'] ?? '') !== 'approved')
                <button
                    type="button"
                    @click="showConfirmApprove = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-green-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approve
                </button>

                <div
                    x-show="showConfirmApprove"
                    x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                    @click.away="showConfirmApprove = false"
                >
                    <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-card">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Approve Assignment?</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Once approved, all selected members will be notified. Roles and assignments will be locked.</p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showConfirmApprove = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-border dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button
                                type="button"
                                wire:click="approve"
                                wire:loading.attr="disabled"
                                @click="showConfirmApprove = false"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-green-500 disabled:opacity-50"
                            >
                                <svg wire:loading.remove wire:target="approve" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg wire:loading wire:target="approve" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                <span wire:loading.remove wire:target="approve">Confirm Approve</span>
                                <span wire:loading wire:target="approve">Approving...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @elseif ($generatedResults && ($generatedResults['status'] ?? '') === 'approved')
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-green-100 px-4 py-2 text-sm font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approved
                </span>
            @endif
        @endif
    </div>
</div>
