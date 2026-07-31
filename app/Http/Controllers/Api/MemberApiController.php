<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class MemberApiController extends Controller
{
    public function index()
    {
        $members = User::publiclyVisible()
            ->with('memberProfile')
            ->paginate(20);

        return response()->json([
            'data' => $members->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'headline' => $user->memberProfile?->headline,
                'bio' => $user->memberProfile?->bio,
                'avatar' => $user->memberProfile?->avatar_url,
            ]),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    public function show(User $user)
    {
        if (! $user->isApproved() || ! $user->show_profile) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $user->load('memberProfile', 'projects');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'headline' => $user->memberProfile?->headline,
                'bio' => $user->memberProfile?->bio,
                'avatar' => $user->memberProfile?->avatar_url,
                'skills' => $user->memberProfile?->skills,
                'projects' => $user->projects->map(fn ($project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                ]),
            ],
        ]);
    }
}
