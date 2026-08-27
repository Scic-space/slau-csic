<div class="space-y-3">
    @if ($activities->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">No activity recorded yet.</p>
    @else
        @foreach ($activities as $activity)
            <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-border bg-background px-4 py-3">
                <div class="mt-0.5 shrink-0">
                    @switch($activity->description)
                        @case('created')
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-xs text-green-600 dark:bg-green-900/30 dark:text-green-400">+</span>
                        @break
                        @case('updated')
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">~</span>
                        @break
                        @case('deleted')
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-xs text-red-600 dark:bg-red-900/30 dark:text-red-400">-</span>
                        @break
                        default
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">•</span>
                    @endswitch
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $activity->description }}</p>
                        <p class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($activity->changes->count())
                        <div class="mt-1 space-y-0.5">
                            @foreach ($activity->changes->get('attributes', []) as $field => $newValue)
                                @php
                                    $old = $activity->changes->get('old', [])[$field] ?? null;
                                @endphp
                                @if ($old !== null && $old !== $newValue)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">{{ $field }}:</span>
                                        <span class="text-red-500 line-through">{{ $old }}</span>
                                        <span class="mx-1">→</span>
                                        <span class="text-green-500">{{ $newValue }}</span>
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if ($activity->causer)
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">by {{ $activity->causer->name }}</p>
                    @endif
                </div>
            </div>
        @endforeach
        @if ($activities->count() >= 50)
            <p class="text-center text-xs text-gray-400 dark:text-gray-500">Showing last 50 entries</p>
        @endif
    @endif
</div>
