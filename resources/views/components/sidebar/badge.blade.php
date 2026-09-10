@props(['for' => ''])

<span
    x-show="$store.sidebarBadges.{{ $for }} > 0"
    x-cloak
    class="ml-auto inline-flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-brand-600 px-1.5 text-[10px] font-semibold leading-none text-white shadow-sm dark:bg-primary dark:text-primary-foreground"
>
    <span x-text="$store.sidebarBadges.{{ $for }} > 99 ? '99+' : $store.sidebarBadges.{{ $for }}"></span>
</span>