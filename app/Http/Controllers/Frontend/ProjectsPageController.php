<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectsPageController extends Controller
{
    public function index(): Response
    {
        $projects = Project::query()
            ->with(['lead', 'members'])
            ->latest()
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'type' => $project->type,
                'status' => $project->status,
                'repository_url' => $project->repository_url,
                'lead' => $project->lead?->only(['id', 'name']),
                'members' => $project->members->map(fn ($m) => $m->only(['id', 'name'])),
            ]);

        $deliveryPillars = [
            [
                'title' => 'Challenge Framing',
                'copy' => 'Each project starts with a clearly defined security, innovation, or campus problem so members understand the mission before they begin building.',
            ],
            [
                'title' => 'Applied Execution',
                'copy' => 'Teams move from planning to hands-on implementation through secure coding, lab testing, design reviews, and documented iteration.',
            ],
            [
                'title' => 'Visible Outcomes',
                'copy' => 'The club presents work as outcomes, lessons, and contribution records so progress is visible to members, leaders, and partners.',
            ],
        ];

        $projectTracks = [
            [
                'name' => 'Secure Build Reviews',
                'focus' => 'Student teams examine web and mobile ideas through a security-first delivery lens.',
            ],
            [
                'name' => 'Campus Problem Solving',
                'focus' => 'Projects are framed around practical institutional needs, operational clarity, and responsible digital practice.',
            ],
            [
                'name' => 'Competition Readiness',
                'focus' => 'Members refine tools, challenge workflows, and team coordination before club competitions and external events.',
            ],
        ];

        return Inertia::render('public/Projects', [
            'projects' => $projects,
            'deliveryPillars' => $deliveryPillars,
            'projectTracks' => $projectTracks,
        ]);
    }
}
