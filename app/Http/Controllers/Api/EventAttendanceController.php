<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Requests\StoreEventAttendanceRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventAttendanceController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function index(Request $request, Event $event)
    {
        if (! $request->user()->can('manage_attendance') && $event->organizer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $event->attendanceRecords()->with('member');

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $attendance = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->through(fn ($record) => [
                'id' => $record->id,
                'member' => ['id' => $record->member->id, 'name' => $record->member->name],
                'status' => $record->status,
                'checked_in_at' => $record->checked_in_at,
                'recorded_at' => $record->recorded_at,
            ]);

        return response()->json($attendance);
    }

    public function store(StoreEventAttendanceRequest $request, Event $event)
    {
        if (! $request->user()->can('manage_attendance') && $event->organizer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($event->start_date && $event->start_date->isFuture()) {
            return response()->json(['error' => 'Cannot mark attendance before the event starts'], 400);
        }

        $attendance = $this->eventService->markAttendance($event, $request->member_id, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded',
            'attendance' => $attendance,
        ]);
    }

    public function bulk(BulkAttendanceRequest $request, Event $event)
    {
        if (! $request->user()->can('manage_attendance') && $event->organizer_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($event->start_date && $event->start_date->isFuture()) {
            return response()->json(['error' => 'Cannot mark attendance before the event starts'], 400);
        }

        $results = $this->eventService->bulkAttendance($event, $request->attendance_data);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function export(Request $request, Event $event)
    {
        if (! $request->user()->can('manage_attendance')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attendance = $event->attendanceRecords()
            ->with('member')
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "Member Name,Status,Checked In At,Recorded At\n";

        foreach ($attendance as $record) {
            $name = $record->member->name;
            if (in_array($name[0] ?? '', ['=', '+', '-', '@', "\t", "\r"])) {
                $name = "'".$name;
            }
            $csv .= "\"{$name}\",{$record->status},{$record->checked_in_at},{$record->recorded_at}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$event->slug}-attendance.csv\"",
        ]);
    }
}
