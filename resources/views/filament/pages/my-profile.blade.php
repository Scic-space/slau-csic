<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @php
            $user = $this->user;
        @endphp

        @if ($user->membership_status === 'inactive' && $user->membership_type !== 'alumni')
            <div class="rounded-lg bg-yellow-50 p-4 text-sm text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                Your membership is inactive.
                <a href="{{ route('membership.renew') }}" class="font-medium underline hover:no-underline">
                    Renew now
                </a>
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <x-filament::button
                tag="a"
                href="{{ route('membership.card') }}"
                target="_blank"
                color="gray"
            >
                View Membership Card
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="{{ url('/admin/manage-fines?user=' . auth()->id()) }}"
                color="gray"
            >
                View My Fines
            </x-filament::button>
        </div>

        @if ($this->activities->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-border dark:bg-card">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Membership History
                </h2>
                <div class="space-y-4">
                    @foreach ($this->activities as $activity)
                        <div class="flex items-start gap-3">
                            <div class="mt-1.5 flex h-2 w-2 shrink-0 rounded-full bg-indigo-400" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $activity['description'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity['date'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
