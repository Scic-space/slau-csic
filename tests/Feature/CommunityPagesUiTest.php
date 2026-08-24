<?php

it('uses the members page spacing across community pages', function (string $view) {
    $markup = file_get_contents(resource_path("views/livewire/{$view}.blade.php"));

    expect($markup)
        ->toContain('py-4 sm:py-5')
        ->toMatch('/(?:mb-4|space-y-3)/')
        ->not->toContain('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8')
        ->not->toContain('mx-auto max-w-4xl px-4 sm:px-6 lg:px-8');
})->with([
    'cabinet voting' => 'election-voting',
    'nominate' => 'election-nominations',
    'applications' => 'election-my-applications',
    'results' => 'election-results',
    'verify vote' => 'verify-receipt',
    'announcements' => 'announcement-listing',
    'polls' => 'poll-listing',
]);

it('uses material symbols on every community page', function (string $view, string $icon) {
    $markup = file_get_contents(resource_path("views/livewire/{$view}.blade.php"));

    expect($markup)
        ->toContain('material-symbols-outlined')
        ->toContain(">{$icon}<");
})->with([
    'cabinet voting' => ['election-voting', 'how_to_vote'],
    'nominate' => ['election-nominations', 'person_add'],
    'applications' => ['election-my-applications', 'assignment_ind'],
    'results' => ['election-results', 'poll'],
    'verify vote' => ['verify-receipt', 'verified_user'],
    'announcements' => ['announcement-listing', 'campaign'],
    'polls' => ['poll-listing', 'ballot'],
]);

it('keeps Google Sans Flex configured globally with fallback fonts', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('family=Google+Sans+Flex')
        ->toContain("--font-sans: 'Google Sans Flex', 'Google Sans', Inter, ui-sans-serif, sans-serif");
});
