<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    protected $listeners = [
        'notification-sent' => 'refreshCount',
        'notification-read' => 'refreshCount',
        'notifications-read-all' => 'refreshCount',
        'notification-updated' => 'refreshCount',
    ];

    public function refreshCount(): void
    {
        $this->unreadCount = Auth::check()
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    }

    public function markAllRead(): void
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
            $this->refreshCount();
            $this->dispatch('notifications-read-all');
        }
    }

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
