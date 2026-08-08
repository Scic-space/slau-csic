<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\User;
use Livewire\Component;

class MemberShow extends Component
{
    use GuardsPendingMembers;

    public ?int $user = null;

    public function mount(int $user): void
    {
        $this->user = $user;
    }

    public function render()
    {
        $user = User::with(['roles', 'memberProfile', 'socialLinks', 'earnedBadges', 'gamificationStats'])
            ->findOrFail($this->user);

        if (! $user->isApproved() || ! $user->show_profile) {
            abort(404);
        }

        $privacy = fn (string $field) => $user->canShowField($field);

        $eventsAttended = $user->eventRegistrations()
            ->where('status', 'attended')
            ->with('event:id,title,start_date,slug,type')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->event->id,
                'title' => $r->event->title,
                'date' => $r->event->start_date?->format('M j, Y'),
                'type' => $r->event->type,
            ]);

        $member = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $privacy('show_email') ? $user->email : null,
            'role_names' => $user->roles->pluck('name'),
            'membership_status' => $user->membership_status,
            'membership_type' => $user->membership_type,
            'profile_photo_url' => $user->profile_photo_url,
            'joined_at' => $user->joined_at?->format('M Y'),
            'program' => $privacy('show_program') ? $user->memberProfile?->program : null,
            'faculty' => $privacy('show_program') ? $user->memberProfile?->faculty : null,
            'year_of_study' => $privacy('show_year') ? $user->memberProfile?->year_of_study : null,
            'bio' => $user->memberProfile?->bio,
            'headline' => $user->memberProfile?->headline,
            'github_username' => $privacy('show_profile') ? $user->socialLinks?->github_username : null,
            'linkedin_url' => $privacy('show_profile') ? $user->socialLinks?->linkedin_url : null,
            'discord_username' => $privacy('show_discord') ? $user->socialLinks?->discord_username : null,
            'score' => $user->gamificationStats?->score,
            'rank' => $user->gamificationStats?->rank,
            'total_sessions_attended' => $user->gamificationStats?->total_sessions_attended,
            'current_streak' => $user->gamificationStats?->current_streak,
            'events_attended' => $eventsAttended,
            'events_attended_count' => $user->eventRegistrations()->where('status', 'attended')->count(),
            'badges' => $user->earnedBadges->map(fn ($b) => [
                'name' => $b->name,
                'description' => $b->description,
                'icon' => $b->icon,
                'earned_at' => $b->pivot->earned_at ? \Carbon\Carbon::parse($b->pivot->earned_at)->format('M j, Y') : null,
            ]),
            'can_be_contacted' => $user->canBeContacted(),
        ];

        return view('livewire.member-show', ['member' => $member]);
    }
}
