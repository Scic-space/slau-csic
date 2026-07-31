<?php

namespace App\Models;

enum BadgeRarity: string
{
    case Common = 'common';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';

    public function label(): string
    {
        return match ($this) {
            self::Common => 'Common',
            self::Rare => 'Rare',
            self::Epic => 'Epic',
            self::Legendary => 'Legendary',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Common => 'gray',
            self::Rare => 'blue',
            self::Epic => 'purple',
            self::Legendary => 'amber',
        };
    }
}
