<?php

namespace App\View\Components\header;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class NotificationDropdown extends Component
{
    public int $unreadCount;

    public function __construct()
    {
        $this->unreadCount = Auth::check()
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    }

    public function render(): View|Closure|string
    {
        return view('components.header.notification-dropdown');
    }
}
