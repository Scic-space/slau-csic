<div class="bg-white dark:bg-card rounded-xl shadow-sm border border-gray-200 dark:border-border">
    @if(($title ?? false) || ($description ?? false))
        <div class="border-b border-gray-200 dark:border-border px-6 py-4">
            @if($title ?? false)
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            @endif
            @if($description ?? false)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="px-6 py-4">
        {{ $slot }}
    </div>
</div>
