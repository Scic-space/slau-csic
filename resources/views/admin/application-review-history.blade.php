<div class="space-y-3">
    @forelse ($reviews as $review)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">{{ $review->user->name ?? 'System' }}</span>
                <span class="text-xs text-gray-500">{{ $review->created_at->format('M j, Y g:i A') }}</span>
            </div>
            <div class="mt-1 text-sm">
                @if ($review->from_status)
                    <span class="text-gray-500">{{ ucfirst($review->from_status) }}</span> &rarr;
                @endif
                <span class="font-semibold">{{ ucfirst($review->to_status) }}</span>
            </div>
            @if ($review->notes)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $review->notes }}</p>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500">No review history yet.</p>
    @endforelse
</div>
