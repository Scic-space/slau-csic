@props([
    'show',
    'onClose',
    'title' => null,
    'width' => 'md',
    'showCloseButton' => true,
    'closeOnOverlayClick' => true,
])

@php
    $desktopWidth = match ($width) {
        'sm' => 'lg:w-[400px]',
        'lg' => 'lg:w-[480px]',
        default => 'lg:w-[440px]',
    };
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    @keydown.escape.window="{{ $onClose }}"
    class="fixed inset-0 z-999999"
    role="dialog"
    aria-modal="true"
    x-trap.noscroll.inert="{{ $show }}"
    {{ $attributes->except('class') }}
>
    {{-- Overlay --}}
    <div
        class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if ($closeOnOverlayClick) @click="{{ $onClose }}" @endif
    ></div>

    {{-- Panel --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div
            @click.stop
            class="relative flex h-full w-[calc(100vw-2rem)] flex-col overflow-y-auto rounded-l-sm bg-white shadow-2xl sm:w-[70vw] {{ $desktopWidth }} dark:bg-popover {{ $attributes->get('class') }}"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            @if ($title || $showCloseButton)
                <div class="flex items-start justify-between border-b border-gray-200 p-5 dark:border-border">
                    @if ($title)
                        <h2 class="text-xl font-bold text-gray-900 dark:text-foreground">{{ $title }}</h2>
                    @endif
                    @if ($showCloseButton)
                        <button
                            type="button"
                            @click="{{ $onClose }}"
                            aria-label="Close"
                            class="ml-4 text-gray-400 transition-colors hover:text-gray-700 dark:text-muted dark:hover:text-foreground"
                        >
                            <span class="material-symbols-outlined" aria-hidden="true">close</span>
                        </button>
                    @endif
                </div>
            @endif

            <div class="flex min-h-0 flex-1 flex-col">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
