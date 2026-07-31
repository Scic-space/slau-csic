<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicMemberProfileController extends Controller
{
    public function __invoke(string $id): Response
    {
        $user = User::with(['roles', 'memberProfile', 'socialLinks'])->findOrFail($id);

        if (! $user->isApproved() || ! $user->show_profile) {
            abort(404);
        }

        return Inertia::render('members/Show', [
            'member' => [
                'id' => $user->id,
                'name' => $user->name,
                'role_names' => $user->roles->pluck('name'),
                'membership_status' => $user->membership_status,
                'membership_type' => $user->membership_type,
                'profile_photo_url' => $user->profile_photo_url,
                'program' => $user->memberProfile?->program,
                'faculty' => $user->memberProfile?->faculty,
                'year_of_study' => $user->memberProfile?->year_of_study,
                'bio' => $user->memberProfile?->bio,
                'headline' => $user->memberProfile?->headline,
                'github_username' => $user->socialLinks?->github_username,
                'linkedin_url' => $user->socialLinks?->linkedin_url,
                'discord_username' => $user->socialLinks?->discord_username,
            ],
        ]);
    }
}
