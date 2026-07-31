<?php

namespace App\Livewire;

use App\Notifications\NotificationTypeConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Notifications')]
class MemberNotifications extends Component
{
    use WithPagination;

    public string $activeTab = 'all';

    public string $activeCategory = 'all';

    protected $listeners = [
        'notificationRead' => '$refresh',
        'notificationDeleted' => '$refresh',
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->activeCategory = 'all';
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
        $this->activeTab = 'all';
        $this->resetPage();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('toast-show', message: 'Notification marked as read', type: 'success');
            $this->dispatch('notification-read');
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('toast-show', message: 'All notifications marked as read', type: 'success');
        $this->dispatch('notifications-read-all');
    }

    public function deleteNotification(string $notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->dispatch('toast-show', message: 'Notification deleted', type: 'success');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = $user->notifications();

        // Tab filter
        if ($this->activeTab === 'unread') {
            $query->whereNull('read_at');
        }

        // Category filter
        if ($this->activeCategory !== 'all') {
            $classes = NotificationTypeConfig::classesInCategory($this->activeCategory);
            if (! empty($classes)) {
                $query->where(function ($q) use ($classes) {
                    foreach ($classes as $class) {
                        $q->orWhere('type', 'like', "%{$class}%");
                    }
                });
            }
        }

        $notifications = $query->latest()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        // Category counts
        $categoryCounts = [
            'all' => $user->notifications()->count(),
            'unread' => $unreadCount,
        ];
        foreach (array_keys(NotificationTypeConfig::categories()) as $category) {
            if (! in_array($category, ['all', 'unread'])) {
                $classes = NotificationTypeConfig::classesInCategory($category);
                $categoryCounts[$category] = $user->notifications()
                    ->where(function ($q) use ($classes) {
                        foreach ($classes as $class) {
                            $q->orWhere('type', 'like', "%{$class}%");
                        }
                    })->count();
            }
        }

        return view('livewire.member-notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
