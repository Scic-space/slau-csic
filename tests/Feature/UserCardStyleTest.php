<?php

it('uses rounded-sm on every shared user card primitive', function () {
    $viewFiles = [
        ...glob(resource_path('views/livewire/*.blade.php')),
        ...glob(resource_path('views/pages/club/*.blade.php')),
    ];

    foreach ($viewFiles as $viewFile) {
        $markup = file_get_contents($viewFile);

        if (! str_contains($markup, 'dashboard-card') && ! str_contains($markup, 'dashboard-stat')) {
            continue;
        }

        preg_match_all('/class="[^"]*dashboard-(?:card|stat)[^"]*"/', $markup, $cardClasses);

        foreach ($cardClasses[0] as $cardClass) {
            expect($cardClass)->toContain('rounded-sm');
        }
    }
});

it('defines consistent TailAdmin-style Material Icon containers for user cards', function () {
    $styles = file_get_contents(resource_path('css/app.css'));

    expect($styles)
        ->toContain('.dashboard-stat .material-symbols-outlined')
        ->toContain('h-9 w-9 shrink-0 items-center justify-center rounded-sm')
        ->toContain('text-[20px]');
});
