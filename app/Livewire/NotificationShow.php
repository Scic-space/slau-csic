<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Notification')]
class NotificationShow extends Component
{
    public DatabaseNotification $notification;

    public function mount(string $notificationId): void
    {
        $user = Auth::user();

        $this->notification = $user->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (is_null($this->notification->read_at)) {
            $this->notification->markAsRead();
            $this->dispatch('notification-read');
        }
    }

    public function markAsRead(): void
    {
        if (is_null($this->notification->read_at)) {
            $this->notification->markAsRead();
            $this->dispatch('notification-read');
            $this->dispatch('toast-show', message: 'Notification marked as read', type: 'success');
        }
    }

    public function deleteNotification(): void
    {
        $this->notification->delete();
        $this->dispatch('toast-show', message: 'Notification deleted', type: 'success');
        $this->redirect(route('notifications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.notification-show');
    }
}
