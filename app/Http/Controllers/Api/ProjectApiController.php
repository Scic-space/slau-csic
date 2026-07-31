<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectApiController extends Controller
{
    public function index()
    {
        $projects = Project::with('lead')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description,
                'type' => $project->type,
                'status' => $project->status,
                'progress_percentage' => $project->progress_percentage,
                'repository_url' => $project->repository_url,
                'lead' => $project->lead?->name,
            ]);

        return response()->json(['data' => $projects]);
    }

    public function show(Project $project)
    {
        $project->load(['lead', 'members']);

        return response()->json([
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'objectives' => $project->objectives,
                'type' => $project->type,
                'status' => $project->status,
                'start_date' => $project->start_date?->toDateString(),
                'end_date' => $project->end_date?->toDateString(),
                'repository_url' => $project->repository_url,
                'documentation_url' => $project->documentation_url,
                'progress_percentage' => $project->progress_percentage,
                'tags' => $project->tags,
                'lead' => $project->lead ? ['id' => $project->lead->id, 'name' => $project->lead->name] : null,
                'members' => $project->members->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'role' => $m->pivot->role,
                ]),
            ],
        ]);
    }
}
