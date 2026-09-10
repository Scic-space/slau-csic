@props([
    'variant' => 'user',
])

@php
    $greetingUser = auth()->user();
    $initialGreeting = \App\Support\TimeOfDayGreeting::forHour((int) now()->format('G'));
@endphp

@if ($greetingUser)
    <div
        x-data="{
            greeting: @js($initialGreeting),
            greetingTimer: null,
            updateGreeting() {
                const hour = new Date().getHours();

                this.greeting = hour >= 5 && hour < 12
                    ? 'Good morning'
                    : hour >= 12 && hour < 17
                        ? 'Good afternoon'
                        : hour >= 17 && hour < 21
                            ? 'Good evening'
                            : 'Good night';
            },
            init() {
                this.updateGreeting();
                this.greetingTimer = window.setInterval(() => this.updateGreeting(), 60_000);
            },
            destroy() {
                window.clearInterval(this.greetingTimer);
            },
        }"
        @class([
            'min-w-0 font-sans leading-tight',
            'flex w-full items-center px-5 py-3 xl:flex-1 xl:justify-center xl:px-4 xl:py-0' => $variant === 'user',
            'admin-topbar-greeting hidden lg:block' => $variant === 'admin-desktop',
            'admin-topbar-greeting admin-topbar-greeting-mobile lg:hidden' => $variant === 'admin-mobile',
        ])
        aria-label="Authenticated user greeting"
    >
        <div class="min-w-0">
            <p class="truncate text-xs font-medium text-gray-500 dark:text-muted-foreground" x-text="greeting">
                {{ $initialGreeting }}
            </p>
            <p class="mt-0.5 truncate text-sm font-semibold text-gray-800 dark:text-foreground">
                {{ $greetingUser->name }}
            </p>
        </div>
    </div>
@endif
