<?php

namespace App\Livewire;

use App\Models\Announcement;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Announcements')]
class AnnouncementListing extends Component
{
    public function render()
    {
        $user = auth()->user();

        $announcements = Announcement::published()
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function (Announcement $a) use ($user) {
                $viewedUserIds = $a->views()->pluck('user_id')->toArray();
                $viewCount = count($viewedUserIds);

                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'slug' => $a->slug,
                    'content' => $a->content,
                    'type' => $a->type,
                    'is_active' => $a->isActive(),
                    'is_expired' => $a->isExpired(),
                    'is_seen' => $user ? in_array($user->id, $viewedUserIds) : false,
                    'view_count' => $viewCount,
                    'published_at' => $a->published_at?->toIso8601String(),
                ];
            });

        return view('livewire.announcement-listing', [
            'announcements' => $announcements,
        ]);
    }
}
