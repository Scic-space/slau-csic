<?php

use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionApiController;
use App\Http\Controllers\Api\CtfApiController;
use App\Http\Controllers\Api\ElectionApiController;
use App\Http\Controllers\Api\EventAnalyticsController;
use App\Http\Controllers\Api\EventAttendanceController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\Api\EventResourceController;
use App\Http\Controllers\Api\ExamApiController;
use App\Http\Controllers\Api\FinanceApiController;
use App\Http\Controllers\Api\GamificationApiController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\MemberApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\TeachingApiController;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile App
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/events', [EventController::class, 'index']);
Route::get('/members', [MemberApiController::class, 'index']);
Route::get('/members/{user}', [MemberApiController::class, 'show']);
Route::get('/projects', [ProjectApiController::class, 'index']);
Route::get('/projects/{project}', [ProjectApiController::class, 'show']);
Route::get('/competitions', [CompetitionApiController::class, 'index']);
Route::get('/competitions/{competition}', [CompetitionApiController::class, 'show']);
Route::get('/announcements', [AnnouncementApiController::class, 'index']);
Route::get('/teaching/sessions', [TeachingApiController::class, 'sessions']);
Route::get('/teaching/sessions/{training}', [TeachingApiController::class, 'sessionShow']);
Route::get('/ctf/competitions', [CtfApiController::class, 'competitions']);
Route::get('/ctf/competitions/{competition}', [CtfApiController::class, 'competitionShow']);
Route::get('/ctf/competitions/{competition}/scoreboard', [CtfApiController::class, 'scoreboard']);
Route::get('/leaderboard', [GamificationApiController::class, 'leaderboard']);
Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $key = 'contact-form:'.$request->ip();
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 3)) {
        return response()->json(['message' => 'Too many attempts. Try again later.'], 429);
    }
    \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'topic' => 'required|string|max:255',
        'message' => 'required|string|max:5000',
    ]);

    ContactMessage::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Thank you for your message!',
    ], 201);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Auth & Profile
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Meetings
    Route::get('/meetings/upcoming', [MeetingController::class, 'upcoming']);
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show']);

    // Attendance
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::get('/user/attendance', [AttendanceController::class, 'myHistory']);

    // Events (protected)
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::get('/events/{event}/edit-permission', [EventController::class, 'editPermission']);
    Route::post('/events/{event}/publish', [EventController::class, 'publish']);
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel']);

    // Event Registration
    Route::post('/events/{event}/register', [EventRegistrationController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/events/{event}/unregister', [EventRegistrationController::class, 'unregister'])->middleware('throttle:10,1');
    Route::post('/events/{event}/cancel-rsvp', [EventRegistrationController::class, 'cancelRsvp'])->middleware('throttle:10,1');
    Route::get('/user/events', [EventRegistrationController::class, 'myEvents']);
    Route::get('/events/{event}/registrations', [EventRegistrationController::class, 'registrations']);
    Route::post('/events/{event}/registrations/bulk', [EventRegistrationController::class, 'bulkRegister']);
    Route::post('/events/{event}/registrations/{memberId}/approve', [EventRegistrationController::class, 'approveRegistration']);
    Route::post('/events/{event}/registrations/{memberId}/reject', [EventRegistrationController::class, 'rejectRegistration']);
    Route::delete('/events/{event}/registrations/{memberId}', [EventRegistrationController::class, 'removeRegistration']);

    // Event Attendance
    Route::get('/events/{event}/attendance', [EventAttendanceController::class, 'index']);
    Route::post('/events/{event}/attendance', [EventAttendanceController::class, 'store']);
    Route::post('/events/{event}/attendance/bulk', [EventAttendanceController::class, 'bulk']);
    Route::get('/events/{event}/attendance/export', [EventAttendanceController::class, 'export']);

    // Prerequisites
    Route::get('/events/{event}/prerequisites', [EventController::class, 'prerequisites']);
    Route::post('/events/{event}/prerequisites', [EventController::class, 'storePrerequisite']);

    // Feedback
    Route::post('/events/{event}/feedback', [EventController::class, 'storeFeedback']);
    Route::get('/events/{event}/feedback', [EventController::class, 'feedback']);

    // Member event history
    Route::get('/members/{member}/event-history', [EventAnalyticsController::class, 'memberHistory']);

    // Event Materials
    Route::get('/events/{event}/materials', [EventResourceController::class, 'index']);
    Route::post('/events/{event}/materials', [EventResourceController::class, 'store']);
    Route::delete('/events/{event}/materials/{resource}', [EventResourceController::class, 'destroy']);

    // Analytics
    Route::get('/events/analytics/summary', [EventAnalyticsController::class, 'summary']);

    // Elections
    Route::get('/elections', [ElectionApiController::class, 'index']);
    Route::get('/elections/{election}', [ElectionApiController::class, 'show']);
    Route::post('/elections/{election}/vote', [ElectionApiController::class, 'castVote'])->middleware('throttle:10,1');

    // Exams
    Route::get('/exams', [ExamApiController::class, 'index']);
    Route::get('/exams/{exam}', [ExamApiController::class, 'show']);
    Route::post('/exams/{exam}/start', [ExamApiController::class, 'start']);
    Route::post('/exams/attempts/{attempt}/submit', [ExamApiController::class, 'submit']);
    Route::get('/exams/attempts/{attempt}/result', [ExamApiController::class, 'result']);

    // Competitions (protected)
    Route::post('/competitions/{competition}/join', [CompetitionApiController::class, 'join']);
    Route::post('/competitions/{competition}/leave', [CompetitionApiController::class, 'leave']);

    // CTF (protected)
    Route::post('/ctf/challenges/{challenge}/submit', [CtfApiController::class, 'submitFlag'])->middleware('throttle:30,1');

    // Teaching (protected)
    Route::post('/teaching/sessions/{training}/enroll', [TeachingApiController::class, 'enroll']);

    // Gamification
    Route::get('/user/points', [GamificationApiController::class, 'myPoints']);
    Route::get('/user/badges', [GamificationApiController::class, 'badges']);

    // Finance
    Route::get('/user/fines', [FinanceApiController::class, 'myFines']);
    Route::get('/fine-types', [FinanceApiController::class, 'fineTypes']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationApiController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
});
