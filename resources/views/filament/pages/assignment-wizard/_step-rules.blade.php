<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 text-sm font-bold text-primary-700 shadow-sm dark:bg-primary-900/40 dark:text-primary-300">3</span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Configure AI Suggestion Rules</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Adjust the importance of each factor. Weights determine which factors matter most.</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-end">
            <button
                type="button"
                wire:click="resetPolicyDefaults"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset defaults
            </button>
        </div>

        @php
            $rules = [
                ['key' => 'skill', 'label' => 'Skill Match', 'desc' => 'Suggest members whose skills match role requirements', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'color' => 'primary'],
                ['key' => 'fairness', 'label' => 'Fairness', 'desc' => "Distribute assignments evenly — don't give everything to the same people", 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'primary'],
                ['key' => 'workload', 'label' => 'Workload Balance', 'desc' => 'Avoid overloading members who are already on other teams', 'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', 'color' => 'primary'],
                ['key' => 'experience', 'label' => 'Experience', 'desc' => 'Favor members with higher rank and more event attendance', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'primary'],
            ];

            $colorClasses = [
                'primary' => ['ring' => 'focus:ring-primary-500/30', 'accent' => 'accent-primary-600', 'bg' => 'bg-primary-500', 'text' => 'text-primary-600 dark:text-primary-400', 'lightBg' => 'bg-primary-50 dark:bg-primary-900/20', 'border' => 'border-primary-200 dark:border-primary-800', 'hover' => 'hover:border-primary-300 dark:hover:border-primary-700'],
            ];

            $barColors = ['skill' => 'bg-primary-500', 'fairness' => 'bg-green-500', 'workload' => 'bg-purple-500', 'experience' => 'bg-orange-500'];
            $barLabels = ['skill' => 'Skill', 'fairness' => 'Fairness', 'workload' => 'Workload', 'experience' => 'Experience'];
            $enabledKeys = array_filter(['skill', 'fairness', 'workload', 'experience'], fn($k) => $policyWeights[$k . '_enabled']);
            $totalWeight = array_sum(array_map(fn($k) => $policyWeights[$k . '_weight'], $enabledKeys)) ?: 1;
        @endphp

        <div class="space-y-4">
            @foreach ($rules as $rule)
                @php
                    $enabledField = $rule['key'] . '_enabled';
                    $weightField = $rule['key'] . '_weight';
                    $c = $colorClasses[$rule['color']];
                @endphp
                <div class="rounded-xl border p-5 shadow-sm transition dark:border-gray-700 {{ $c['border'] . ' ' . $c['hover'] }} {{ !$policyWeights[$enabledField] ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $c['lightBg'] }}">
                                <svg class="h-5 w-5 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rule['icon'] }}"/></svg>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $rule['label'] }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $rule['desc'] }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model="policyWeights.{{ $enabledField }}" class="peer sr-only">
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all peer-checked:bg-primary-600 peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700"></div>
                        </label>
                    </div>
                    @if ($policyWeights[$enabledField])
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-12 shrink-0 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Weight</span>
                            <input
                                type="range"
                                wire:model.live="policyWeights.{{ $weightField }}"
                                min="0"
                                max="100"
                                class="h-2 w-full appearance-none rounded-lg bg-gray-200 {{ $c['accent'] }} dark:bg-gray-700"
                            >
                            <span class="w-12 shrink-0 text-right text-sm font-bold {{ $c['text'] }}">{{ $policyWeights[$weightField] }}%</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-900/20">
            <p class="mb-3 text-xs font-bold text-gray-600 dark:text-gray-400">Weight distribution</p>
            <div class="flex h-4 overflow-hidden rounded-full bg-gray-200 shadow-inner dark:bg-gray-700">
                @foreach (['skill', 'fairness', 'workload', 'experience'] as $key)
                    @if ($policyWeights[$key . '_enabled'])
                        <div
                            class="{{ $barColors[$key] }} transition-all duration-500 first:rounded-l-full last:rounded-r-full"
                            style="width: {{ ($policyWeights[$key . '_weight'] / $totalWeight) * 100 }}%"
                            title="{{ $barLabels[$key] }}: {{ $policyWeights[$key . '_weight'] }}%"
                        ></div>
                    @endif
                @endforeach
            </div>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5">
                @foreach (['skill', 'fairness', 'workload', 'experience'] as $key)
                    @if ($policyWeights[$key . '_enabled'])
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                            <span class="inline-block h-2.5 w-2.5 rounded-full {{ $barColors[$key] }}"></span>
                            {{ $barLabels[$key] }}: {{ $policyWeights[$key . '_weight'] }}%
                        </span>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-primary-200 bg-primary-50/50 p-3 dark:border-primary-800 dark:bg-primary-900/10">
            <p class="text-xs text-primary-700 dark:text-primary-300">
                <strong>You stay in control.</strong> These rules only affect the AI auto-fill suggestion. You can override or adjust any suggestion before approving.
            </p>
        </div>
    </div>
</div>
