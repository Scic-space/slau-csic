<?php

it('sets the theme background before external assets load', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

    expect($layout)
        ->toContain('html.dark, html.dark body { background-color: oklch(18% 0.035 255); }')
        ->toContain('class="bg-background text-foreground"')
        ->toContain("document.documentElement.classList.add('is-page-navigating')")
        ->toContain("document.documentElement.classList.remove('is-page-navigating')");
});

it('loads fonts once with preconnect and display swap', function () {
    $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    $css = file_get_contents(resource_path('css/app.css'));

    expect($layout)
        ->toContain('rel="preconnect" href="https://fonts.googleapis.com"')
        ->toContain('family=Google+Sans+Flex')
        ->toContain('display=swap')
        ->and($css)->not->toContain("@import url('https://fonts.googleapis.com");
});

it('lazy loads charts and Preline instead of adding them to the initial bundle', function () {
    $javascript = file_get_contents(resource_path('js/app.js'));

    expect($javascript)
        ->toContain("import('apexcharts')")
        ->toContain("import('preline')")
        ->not->toContain("import ApexCharts from 'apexcharts'")
        ->not->toContain("import 'preline'");
});

it('uses Livewire navigation for notification links', function () {
    $dropdown = file_get_contents(resource_path('views/components/header/notification-dropdown.blade.php'));

    expect($dropdown)
        ->toContain('<a wire:navigate')
        ->toContain('href="{{ route(\'notifications.index\') }}" wire:navigate')
        ->not->toContain('window.location.href = this.href');
});
