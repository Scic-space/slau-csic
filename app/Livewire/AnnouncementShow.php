<?php

namespace App\Livewire;

use App\Models\Announcement;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Announcement')]
class AnnouncementShow extends Component
{
    public ?array $announcement = null;

    public function mount(string $slug): void
    {
        $announcement = Announcement::published()
            ->where('slug', $slug)
            ->with('author')
            ->first();

        if (! $announcement) {
            abort(404);
        }

        $user = auth()->user();
        if ($user) {
            $announcement->markAsViewedBy($user);
        }

        $this->announcement = [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'type' => $announcement->type,
            'is_active' => $announcement->isActive(),
            'is_expired' => $announcement->isExpired(),
            'published_at' => $announcement->published_at?->toIso8601String(),
            'expires_at' => $announcement->expires_at?->toIso8601String(),
            'author' => $announcement->author?->name ?? 'Administration',
        ];
    }

    public function render()
    {
        return view('livewire.announcement-show');
    }
}
