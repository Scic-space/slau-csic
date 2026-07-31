<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Attendance Calendar')]
class AttendanceCalendar extends Component
{
    public function render()
    {
        $user = auth()->user()->load('attendance.meeting');

        $records = $user->attendance()
            ->whereHas('meeting')
            ->with('meeting')
            ->get();

        $calendarEvents = $records->map(fn ($a) => [
            'id' => (string) $a->id,
            'title' => $a->meeting->title.' ('.$a->status.')',
            'start' => $a->meeting->scheduled_at->toIso8601String(),
            'color' => match ($a->status) {
                'present' => '#22c55e',
                'late' => '#eab308',
                'absent' => '#ef4444',
                default => '#6b7280',
            },
            'textColor' => '#ffffff',
            'status' => $a->status,
            'meeting_title' => $a->meeting->title,
            'check_in_time' => $a->checked_in_at?->format('M d, Y g:i A'),
            'location' => $a->meeting->location,
            'notes' => $a->notes,
        ]);

        $stats = [
            'total' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'rate' => $user->getAttendanceRate(),
            'streak' => $user->current_streak,
        ];

        return view('livewire.attendance-calendar', [
            'records' => $calendarEvents,
            'stats' => $stats,
        ]);
    }
}
