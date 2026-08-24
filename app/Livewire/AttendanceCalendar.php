<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPendingMembers;
use App\Models\CalendarReminder;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Attendance Calendar')]
class AttendanceCalendar extends Component
{
    use GuardsPendingMembers;

    public bool $isReminderDrawerOpen = false;

    public ?int $editingReminderId = null;

    public string $reminderTitle = '';

    public string $reminderColor = 'primary';

    public string $reminderStartsOn = '';

    public string $reminderEndsOn = '';

    public function openCreateReminder(?string $date = null): void
    {
        $this->resetReminderForm();
        $selectedDate = $date ?? now()->toDateString();
        $this->reminderStartsOn = $selectedDate;
        $this->reminderEndsOn = $selectedDate;
        $this->isReminderDrawerOpen = true;
    }

    public function editReminder(int $reminderId): void
    {
        $reminder = auth()->user()->calendarReminders()->findOrFail($reminderId);

        $this->editingReminderId = $reminder->id;
        $this->reminderTitle = $reminder->title;
        $this->reminderColor = $reminder->color;
        $this->reminderStartsOn = $reminder->starts_on->toDateString();
        $this->reminderEndsOn = $reminder->ends_on->toDateString();
        $this->isReminderDrawerOpen = true;
    }

    public function saveReminder(): void
    {
        $validated = $this->validate([
            'reminderTitle' => ['required', 'string', 'max:120'],
            'reminderColor' => ['required', Rule::in(['danger', 'success', 'primary', 'warning'])],
            'reminderStartsOn' => ['required', 'date'],
            'reminderEndsOn' => ['required', 'date', 'after_or_equal:reminderStartsOn'],
        ]);

        $reminder = auth()->user()->calendarReminders()->updateOrCreate(
            ['id' => $this->editingReminderId],
            [
                'title' => $validated['reminderTitle'],
                'color' => $validated['reminderColor'],
                'starts_on' => $validated['reminderStartsOn'],
                'ends_on' => $validated['reminderEndsOn'],
            ],
        );

        $this->dispatch('calendar-reminder-saved', reminder: $this->reminderEvent($reminder));
        $this->closeReminderDrawer();
    }

    public function deleteReminder(): void
    {
        abort_unless($this->editingReminderId !== null, 404);

        $reminder = auth()->user()->calendarReminders()->findOrFail($this->editingReminderId);
        $reminderId = $reminder->id;
        $reminder->delete();

        $this->dispatch('calendar-reminder-deleted', reminderId: $reminderId);
        $this->closeReminderDrawer();
    }

    public function closeReminderDrawer(): void
    {
        $this->isReminderDrawerOpen = false;
        $this->resetReminderForm();
    }

    public function render(): View
    {
        $user = auth()->user()->load('attendance.meeting');

        $records = $user->attendance()
            ->whereHas('meeting')
            ->with('meeting')
            ->get();

        $attendanceEvents = $records->map(fn ($a) => [
            'id' => 'attendance-'.$a->id,
            'record_id' => $a->id,
            'type' => 'attendance',
            'title' => $a->meeting->title.' ('.$a->status.')',
            'start' => $a->meeting->scheduled_at->toIso8601String(),
            'end' => $a->meeting->scheduled_at->toIso8601String(),
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

        $reminderEvents = $user->calendarReminders()
            ->orderBy('starts_on')
            ->get()
            ->map(fn (CalendarReminder $reminder) => $this->reminderEvent($reminder));

        $calendarEvents = $attendanceEvents->concat($reminderEvents)->values();

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

    protected function resetReminderForm(): void
    {
        $this->reset([
            'editingReminderId',
            'reminderTitle',
            'reminderStartsOn',
            'reminderEndsOn',
        ]);
        $this->reminderColor = 'primary';
        $this->resetValidation();
    }

    protected function reminderColorHex(string $color): string
    {
        return match ($color) {
            'danger' => '#ef4444',
            'success' => '#22c55e',
            'warning' => '#f59e0b',
            default => '#465fff',
        };
    }

    /** @return array{id: int, type: string, title: string, start: string, end: string, color: string, color_name: string, textColor: string} */
    protected function reminderEvent(CalendarReminder $reminder): array
    {
        return [
            'id' => $reminder->id,
            'type' => 'reminder',
            'title' => $reminder->title,
            'start' => $reminder->starts_on->toDateString(),
            'end' => $reminder->ends_on->toDateString(),
            'color' => $this->reminderColorHex($reminder->color),
            'color_name' => $reminder->color,
            'textColor' => '#ffffff',
        ];
    }
}
