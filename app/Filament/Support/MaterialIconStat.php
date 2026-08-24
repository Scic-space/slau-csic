<?php

namespace App\Filament\Support;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MaterialIconStat extends Stat
{
    public function getIcon(): ?Htmlable
    {
        $icon = parent::getIcon();

        if (blank($icon)) {
            return null;
        }

        return new HtmlString('<span class="material-symbols-outlined" aria-hidden="true">'.e((string) $icon).'</span>');
    }

    public function getDescriptionIcon(): string|\BackedEnum|Htmlable|null
    {
        $icon = parent::getDescriptionIcon();

        if (blank($icon) || Str::startsWith((string) $icon, 'heroicon-')) {
            return $icon;
        }

        return new HtmlString('<span class="material-symbols-outlined" aria-hidden="true">'.e((string) $icon).'</span>');
    }
}
