<div class="py-6">
    <div class="mx-auto max-w-lg space-y-6">
        {{-- Header --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-500">Vote Verification</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Verify your vote receipt
            </h1>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                Enter the receipt code you received after casting your vote to confirm
                that your vote was recorded in the system.
            </p>

            <form wire:submit="verify" class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Receipt Code
                    </label>
                    <input
                        type="text"
                        wire:model="receiptCode"
                        placeholder="e.g. ABC123XYZ789"
                        maxlength="64"
                        required
                        class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-center font-mono text-lg tracking-widest text-gray-900 placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-emerald-400"
                    />
                    @error('receiptCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 transition-colors"
                >
                    <span wire:loading.remove>Verify Vote</span>
                    <span wire:loading>Verifying...</span>
                </button>
            </form>
        </div>

        {{-- Error --}}
        @if ($error)
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-800">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-red-900 dark:text-red-200">Vote not found</h2>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                            Double-check your receipt code and try again. If you believe this is an error, contact the administrators.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Success --}}
        @if ($result)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-800">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-emerald-900 dark:text-emerald-200">
                            Vote verified successfully!
                        </h2>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">
                            Your vote has been confirmed in the system.
                        </p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 rounded-lg border border-emerald-200 bg-white p-4 dark:border-emerald-700 dark:bg-gray-800/50">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Election</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $result['election_title'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Position</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $result['election_position'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Candidate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $result['candidate_name'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Voted at</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $result['voted_at'] }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
