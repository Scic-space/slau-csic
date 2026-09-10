<?php

namespace App\Support;

class TimeOfDayGreeting
{
    public static function forHour(int $hour): string
    {
        $normalizedHour = (($hour % 24) + 24) % 24;

        return match (true) {
            $normalizedHour >= 5 && $normalizedHour < 12 => 'Good morning',
            $normalizedHour >= 12 && $normalizedHour < 17 => 'Good afternoon',
            $normalizedHour >= 17 && $normalizedHour < 21 => 'Good evening',
            default => 'Good night',
        };
    }
}
