<?php

it('uses the members page spacing across teaching pages', function (string $view) {
    $markup = file_get_contents(resource_path("views/livewire/{$view}.blade.php"));

    expect($markup)
        ->toContain('py-4 sm:py-5')
        ->toContain('mb-4')
        ->not->toContain('mx-auto max-w-7xl px-4 sm:px-6 lg:px-8');
})->with([
    'dashboard' => 'instructor-dashboard',
    'trainings' => 'instructor-trainings',
    'sessions' => 'instructor-sessions',
    'materials' => 'instructor-materials',
    'grade book' => 'my-grades',
    'portfolios' => 'teacher-portfolios',
]);

it('uses material symbols in every teaching page', function (string $view, string $icon) {
    $markup = file_get_contents(resource_path("views/livewire/{$view}.blade.php"));

    expect($markup)
        ->toContain('material-symbols-outlined')
        ->toContain(">{$icon}<");
})->with([
    'dashboard' => ['instructor-dashboard', 'co_present'],
    'trainings' => ['instructor-trainings', 'model_training'],
    'sessions' => ['instructor-sessions', 'event_note'],
    'materials' => ['instructor-materials', 'menu_book'],
    'grade book' => ['my-grades', 'grade'],
    'portfolios' => ['teacher-portfolios', 'work'],
]);

it('loads Google Sans Flex globally with suitable fallbacks', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('family=Google+Sans+Flex')
        ->toContain("--font-sans: 'Google Sans Flex', 'Google Sans', Inter, ui-sans-serif, sans-serif");
});
