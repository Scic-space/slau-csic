<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;

class TeachingApiController extends Controller
{
    public function sessions()
    {
        $sessions = Training::published()
            ->with('instructor')
            ->withCount(['modules', 'enrollments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'category' => $t->category,
                'difficulty' => $t->difficulty,
                'instructor' => $t->instructor?->name,
                'modules_count' => $t->modules_count,
                'enrollments_count' => $t->enrollments_count,
                'is_full' => $t->is_full,
            ]);

        return response()->json(['data' => $sessions]);
    }

    public function sessionShow(Training $training)
    {
        if (! $training->is_published) {
            return response()->json(['error' => 'Session not available'], 404);
        }

        $training->load(['instructor', 'modules']);

        return response()->json([
            'data' => [
                'id' => $training->id,
                'title' => $training->title,
                'description' => $training->description,
                'category' => $training->category,
                'difficulty' => $training->difficulty,
                'instructor' => $training->instructor ? [
                    'id' => $training->instructor->id,
                    'name' => $training->instructor->name,
                ] : null,
                'modules' => $training->modules->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'order' => $m->order,
                ]),
                'max_enrollments' => $training->max_enrollments,
                'available_from' => $training->available_from?->toISOString(),
                'available_until' => $training->available_until?->toISOString(),
            ],
        ]);
    }

    public function enroll(Request $request, Training $training)
    {
        if ($training->is_full) {
            return response()->json(['error' => 'Session is full'], 400);
        }

        $existing = $training->enrollments()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return response()->json(['error' => 'Already enrolled'], 409);
        }

        $training->enrollments()->create([
            'user_id' => $request->user()->id,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in session',
        ], 201);
    }
}
