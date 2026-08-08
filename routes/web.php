<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClubPortalController;
use App\Http\Controllers\Frontend\ProjectsPageController;
use App\Http\Controllers\Frontend\PublicMemberPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes

require __DIR__.'/inertia.php';

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/club/competitions', [ClubPortalController::class, 'competitions'])->name('portal.competitions');
    Route::get('/club/ctf-arena', [ClubPortalController::class, 'ctfArena'])->name('portal.ctf');
    Route::get('/club/classes', [ClubPortalController::class, 'classes'])->name('portal.classes');
    Route::get('/club/resources/{clubResource:slug}', [ClubPortalController::class, 'showResource'])->name('portal.resources.show');
    Route::post('/club/resources/{clubResource}/progress', [ClubPortalController::class, 'updateProgress'])->name('portal.progress.update');

    // Attendance Routes
    Route::get('/attendance/verify/{code}', [AttendanceController::class, 'showVerifyPage'])->name('attendance.verify');
    Route::post('/attendance/verify/{code}', [AttendanceController::class, 'processCheckIn']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/user-profile', '/profile')->name('user-profile');
});

// Admin routes are now handled by Filament panels

// CTF MEMBER ROUTES
Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/ctf/files/{file}/download', [App\Http\Controllers\CtfController::class, 'downloadFile'])->name('ctf.file.download');

    Route::get('/ctf', [App\Http\Controllers\CtfController::class, 'index'])->name('ctf.index');
    Route::get('/ctf/dashboard', [App\Http\Controllers\CtfController::class, 'dashboard'])->name('ctf.dashboard');
    Route::get('/ctf/{competition:slug}', [App\Http\Controllers\CtfController::class, 'show'])->name('ctf.competition');
    Route::post('/ctf/{competition:slug}/challenges/{challenge:slug}/submit', [App\Http\Controllers\CtfController::class, 'submit'])->name('ctf.submit')->middleware('throttle:30,1');
    Route::get('/ctf/{competition:slug}/scoreboard', [App\Http\Controllers\CtfController::class, 'scoreboard'])->name('ctf.scoreboard');
    Route::get('/ctf/{competition:slug}/scoreboard/export', [App\Http\Controllers\CtfController::class, 'exportScoreboard'])->name('ctf.scoreboard.export');
    Route::get('/ctf/{competition:slug}/challenges/{challenge:slug}/writeups', [App\Http\Controllers\CtfController::class, 'writeups'])->name('ctf.writeups');
    Route::get('/ctf/{competition:slug}/challenges/{challenge:slug}/writeup', [App\Http\Controllers\CtfController::class, 'writeup'])->name('ctf.writeup');
    Route::post('/ctf/{competition:slug}/challenges/{challenge:slug}/writeup', [App\Http\Controllers\CtfController::class, 'submitWriteup'])->name('ctf.writeup.submit');

    // Hint purchasing
    Route::post('/ctf/{competition:slug}/challenges/{challenge:slug}/hint', [App\Http\Controllers\CtfController::class, 'purchaseHint'])->name('ctf.hint.purchase');

    // Team management
    Route::post('/ctf/{competition:slug}/teams/create', [App\Http\Controllers\CtfController::class, 'createTeam'])->name('ctf.team.create');
    Route::post('/ctf/{competition:slug}/teams/join', [App\Http\Controllers\CtfController::class, 'joinTeam'])->name('ctf.team.join');
    Route::post('/ctf/{competition:slug}/teams/leave', [App\Http\Controllers\CtfController::class, 'leaveTeam'])->name('ctf.team.leave');
    Route::post('/ctf/{competition:slug}/teams/disband', [App\Http\Controllers\CtfController::class, 'disbandTeam'])->name('ctf.team.disband');
    Route::post('/ctf/{competition:slug}/teams/transfer-captaincy', [App\Http\Controllers\CtfController::class, 'transferCaptaincy'])->name('ctf.team.transfer-captaincy');
    Route::post('/ctf/{competition:slug}/teams/settings', [App\Http\Controllers\CtfController::class, 'updateTeamSettings'])->name('ctf.team.settings');
});

// FRONTEND ROUTES - Cybersecurity Club Website

Route::get('/about', App\Http\Controllers\PublicNetworkingController::class)->name('about');

Route::get('/team', function () {
    return Inertia::render('public/Team');
})->name('team');

Route::get('/projects', [ProjectsPageController::class, 'index'])->name('projects');
Route::get('/club-members', [PublicMemberPageController::class, 'index'])->name('members.public');
Route::get('/club-members/{user}', [PublicMemberPageController::class, 'show'])->name('members.public.show');

Route::get('/contact', function () {
    return Inertia::render('public/Contact');
})->name('contact');

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'send'])->middleware('throttle:3,3600');

Route::impersonate();
