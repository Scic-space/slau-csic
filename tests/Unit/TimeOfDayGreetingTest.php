<?php

use App\Support\TimeOfDayGreeting;

it('returns the greeting for every time period boundary', function (int $hour, string $expectedGreeting) {
    expect(TimeOfDayGreeting::forHour($hour))->toBe($expectedGreeting);
})->with([
    'night at midnight' => [0, 'Good night'],
    'night before five' => [4, 'Good night'],
    'morning from five' => [5, 'Good morning'],
    'morning before noon' => [11, 'Good morning'],
    'afternoon from noon' => [12, 'Good afternoon'],
    'afternoon before five' => [16, 'Good afternoon'],
    'evening from five' => [17, 'Good evening'],
    'evening before nine' => [20, 'Good evening'],
    'night from nine' => [21, 'Good night'],
    'night before midnight' => [23, 'Good night'],
]);
