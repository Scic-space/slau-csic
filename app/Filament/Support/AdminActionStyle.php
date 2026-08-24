<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class AdminActionStyle
{
    public static function apply(Action $action, string $label, string $icon, string $color): Action
    {
        return $action
            ->label($label)
            ->icon(new HtmlString('<span class="material-symbols-outlined text-[20px]" aria-hidden="true">'.e($icon).'</span>'))
            ->iconButton()
            ->color($color)
            ->tooltip($label)
            ->extraAttributes([
                'aria-label' => $label,
                'title' => $label,
                'class' => 'rounded-sm transition-colors',
            ]);
    }
}
