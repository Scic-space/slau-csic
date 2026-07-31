<?php

namespace App\Http\Controllers;

use App\Models\EventAttendance;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventCheckInController extends Controller
{
    public function showScanPage()
    {
        $user = auth()->user();
        abort_unless($user && $user->can('manage_attendance'), 403);

        return view('events.checkin-scan');
    }

    public function processCheckIn(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->can('manage_attendance'), 403);

        $request->validate(['code' => 'required|alpha_num|size:16']);

        $code = strtoupper($request->input('code'));

        $registration = EventRegistration::where('check_in_code', $code)
            ->with(['event', 'user'])
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid check-in code.',
            ], 404);
        }

        $event = $registration->event;

        if (! in_array($event->status, ['published', 'scheduled', 'ongoing'])) {
            return response()->json([
                'success' => false,
                'message' => 'This event is not active.',
            ], 403);
        }

        if ($registration->hasAttended()) {
            return response()->json([
                'success' => false,
                'message' => 'This participant has already checked in.',
                'registration' => [
                    'name' => $registration->user->name,
                    'event' => $event->title,
                    'checked_in_at' => $registration->attended_at?->toIso8601String(),
                ],
            ], 409);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $event) {
            $registration->update([
                'attended_at' => now(),
                'status' => 'attended',
            ]);

            EventAttendance::create([
                'event_id' => $event->id,
                'member_id' => $registration->user_id,
                'status' => 'present',
                'checked_in_at' => now(),
                'recorded_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful!',
            'registration' => [
                'name' => $registration->user->name,
                'event' => $event->title,
                'checked_in_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
