<?php

use App\Http\Controllers\Auth\InertiaAuthController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\EventShowController;
use App\Http\Controllers\ExamCertificateDownloadController;
use App\Http\Controllers\ExamTakeController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicHomeController;
use App\Livewire\AnnouncementListing;
use App\Livewire\AttendanceCalendar;
use App\Livewire\CompetitionListing;
use App\Livewire\CompetitionShow;
use App\Livewire\ElectionMyApplications;
use App\Livewire\ElectionNominations;
use App\Livewire\ElectionResults;
use App\Livewire\ElectionShow;
use App\Livewire\ElectionVoting;
use App\Livewire\EventCalendar;
use App\Livewire\EventCreate;
use App\Livewire\EventDetails;
use App\Livewire\EventEdit;
use App\Livewire\EventListing;
use App\Livewire\ExamCertificates;
use App\Livewire\ExamListing;
use App\Livewire\InstructorDashboard;
use App\Livewire\InstructorMaterials;
use App\Livewire\InstructorSessions;
use App\Livewire\InstructorTrainings;
use App\Livewire\MemberDashboard;
use App\Livewire\MemberDirectory;
use App\Livewire\MemberNotifications;
use App\Livewire\MemberProfile;
use App\Livewire\MyEvents;
use App\Livewire\MyGrades;
use App\Livewire\MyTransactions;
use App\Livewire\NotificationShow;
use App\Livewire\SupportPage;
use App\Livewire\TeacherPortfolios;
use App\Livewire\TeachingSessionShow;
use App\Livewire\TrainingListing;
use App\Livewire\TrainingShow;
use App\Livewire\VerifyReceipt;
use Illuminate\Support\Facades\Route;

// Public site routes
Route::get('/', PublicHomeController::class)->name('home');
Route::get('/competitions', CompetitionListing::class)->name('competitions.index');
Route::get('/competitions/{competition}', CompetitionShow::class)->name('competitions.show');
Route::get('/announcements', AnnouncementListing::class)->name('announcements.index');
Route::get('/announcements/{slug}', \App\Livewire\AnnouncementShow::class)->name('announcements.show');
Route::get('/news', [\App\Http\Controllers\PublicNewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [\App\Http\Controllers\PublicNewsController::class, 'show'])->name('news.show');
Route::get('/leaderboard', \App\Http\Controllers\PublicLeaderboardController::class)->name('leaderboard.index');
Route::get('/ctf-arena', \App\Http\Controllers\PublicCtfController::class)->name('ctf-arena');
Route::get('/ctf-arena/testimonial', \App\Livewire\SubmitTestimonial::class)->name('ctf.testimonial')->middleware('auth');
Route::get('/workshops', \App\Http\Controllers\PublicWorkshopController::class)->name('workshops');
Route::get('/certificates/verify/{code}', [CertificateVerificationController::class, 'show'])->name('certificates.verify');

// Public routes
Route::get('/events/calendar', EventCalendar::class)->name('events.calendar')->middleware('auth');
Route::get('/attendance/calendar', AttendanceCalendar::class)->name('attendance.calendar')->middleware('auth');
Route::get('/members', MemberDirectory::class)->name('members.index');
Route::get('/events', EventListing::class)->name('events.index');
Route::get('/events/{event:slug}', EventDetails::class)->name('events.show');
Route::get('/events/{event:slug}/certificate/{registration}', App\Http\Controllers\EventCertificateController::class)
    ->name('events.certificate')
    ->middleware(['auth', 'verified']);
Route::post('/events/{event:slug}/rsvp', [EventShowController::class, 'rsvp'])->name('events.rsvp')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::post('/events/{event:slug}/cancel-rsvp', [EventShowController::class, 'cancelRsvp'])->name('events.cancel-rsvp')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::post('/events/{event:slug}/register', [EventShowController::class, 'register'])->name('events.register')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::post('/events/{event:slug}/unregister', [EventShowController::class, 'unregister'])->name('events.unregister')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::post('/events/{event:slug}/feedback', [EventShowController::class, 'storeFeedback'])->name('events.feedback')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::get('/events/create', EventCreate::class)->name('events.create')->middleware('auth');
Route::get('/events/{event:slug}/edit', EventEdit::class)->name('events.edit')->middleware('auth');
Route::get('/organizer/dashboard', \App\Livewire\OrganizerDashboard::class)->name('organizer.dashboard')->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/auth/login', [InertiaAuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/auth/login', [InertiaAuthController::class, 'login']);
    Route::get('/auth/register', [InertiaAuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/auth/register', [InertiaAuthController::class, 'register']);
    Route::get('/auth/forgot-password', [InertiaAuthController::class, 'showForgotPassword'])->name('auth.forgot-password');
    Route::post('/auth/forgot-password', [InertiaAuthController::class, 'sendResetLink']);
});

Route::get('/events/checkin', App\Http\Controllers\EventCheckInController::class.'@showScanPage')
    ->name('events.checkin')
    ->middleware(['auth', 'throttle:30,1']);
Route::post('/events/checkin', App\Http\Controllers\EventCheckInController::class.'@processCheckIn')
    ->name('events.checkin.process')
    ->middleware(['auth', 'throttle:10,1']);

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [InertiaAuthController::class, 'logout'])->name('auth.logout');

    Route::get('/auth/verify-email', [InertiaAuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/auth/verify-email/resend', [InertiaAuthController::class, 'resendVerification'])->name('verification.resend');

    Route::get('/dashboard', MemberDashboard::class)
        ->name('dashboard');
    Route::get('/my-events', MyEvents::class)->name('my-events');
    Route::get('/grades', MyGrades::class)->name('grades.index');
    Route::get('/membership-card', App\Http\Controllers\MembershipCardController::class)->name('membership.card');
    Route::get('/profile', MemberProfile::class)->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/membership/leave', function () {
        $user = auth()->user();
        $user->leave();

        return redirect()->back()->with('success', 'You have left the club.');
    })->middleware('throttle:6,1')->name('membership.leave');

    Route::post('/membership/renew', function () {
        $user = auth()->user();
        $user->renew();

        return redirect()->back()->with('success', 'Your membership has been renewed.');
    })->middleware('throttle:6,1')->name('membership.renew');

    Route::get('/notifications', MemberNotifications::class)->name('notifications.index');
    Route::get('/notifications/{notificationId}', NotificationShow::class)->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/support', SupportPage::class)->name('support');

    // Training routes
    Route::get('/trainings', TrainingListing::class)->name('trainings.index');
    Route::get('/trainings/{training:slug}', TrainingShow::class)->name('trainings.show');

    // Resource Library
    Route::get('/resources', \App\Livewire\ResourceLibrary::class)->name('resources.index');

    // Teaching session routes
    Route::get('/classes/{meeting}', TeachingSessionShow::class)->name('classes.show');

    Route::get('/members/{user}', \App\Livewire\MemberShow::class)->name('members.show');
    Route::get('/fines', \App\Livewire\MyFines::class)->name('fines.index');
    Route::get('/fines/export/csv', [\App\Http\Controllers\FineExportController::class, 'csv'])->name('fines.export.csv');
    Route::post('/fines/{fine}/appeal', [\App\Http\Controllers\MemberFinesController::class, 'appeal'])->name('fines.appeal');
    Route::get('/fines/{fine}/notice', \App\Http\Controllers\FineNoticeController::class.'@download')->name('fines.notice');
    Route::get('/fines/payments/{payment}/receipt', \App\Http\Controllers\FineReceiptController::class.'@download')->name('fines.payments.receipt');
    Route::get('/my-transactions', MyTransactions::class)->name('my-transactions');
    Route::get('/my-transactions/export/csv', [\App\Http\Controllers\TransactionExportController::class, 'csv'])->name('transactions.export.csv');
    Route::get('/treasurer', \App\Livewire\TreasurerDashboard::class)->name('treasurer.dashboard')->middleware('role:admin|treasurer|president|super-admin');

    Route::get('/account-statement', [\App\Http\Controllers\AccountStatementController::class, 'download'])->name('account.statement');
    Route::get('/account-statement/{user}', [\App\Http\Controllers\AccountStatementController::class, 'download'])->name('account.statement.user')->middleware('role:admin|treasurer|president|super-admin');

    Route::get('/exams', ExamListing::class)->name('exams.index');
    Route::get('/exams/{exam}/take', \App\Livewire\ExamTake::class)->name('exams.take');
    Route::post('/exams/{exam}/take/answer', [ExamTakeController::class, 'saveAnswer'])->name('exams.save-answer');
    Route::post('/exams/{exam}/take/submit', [ExamTakeController::class, 'submit'])->name('exams.submit');
    Route::get('/exams/attempts/{attempt}/result', \App\Livewire\ExamResult::class)->name('exams.result');
    Route::get('/exams/certificates', ExamCertificates::class)->name('exams.certificates');
    Route::get('/exams/certificates/{eligibility}/download', ExamCertificateDownloadController::class)->name('exams.certificates.download');

    Route::get('/voting', ElectionVoting::class)->name('voting.index');
    Route::get('/voting/nominations', ElectionNominations::class)->name('voting.nominations');
    Route::get('/voting/my-applications', ElectionMyApplications::class)->name('voting.my-applications');
    Route::get('/voting/results', ElectionResults::class)->name('voting.results');
    Route::get('/vote/verify', VerifyReceipt::class)->name('voting.verify.form');
    Route::get('/voting/{slug}', ElectionShow::class)->name('voting.show');

    // Polls
    Route::get('/polls', \App\Livewire\PollListing::class)->name('polls.index');
    Route::get('/polls/{slug}', \App\Livewire\PollShow::class)->name('polls.show');

    // Instructor routes
    Route::get('/instructor', InstructorDashboard::class)->name('instructor.dashboard')->middleware('can:content.view');
    Route::get('/instructor/trainings', InstructorTrainings::class)->name('instructor.trainings')->middleware('can:content.view');
    Route::get('/instructor/sessions', InstructorSessions::class)->name('instructor.sessions')->middleware('can:content.view');
    Route::get('/instructor/materials', InstructorMaterials::class)->name('instructor.materials')->middleware('can:content.view');
    Route::get('/teacher/portfolios', TeacherPortfolios::class)->name('teacher.portfolios')->middleware('can:portfolio.view');
    Route::get('/portfolios/{slug}', \App\Livewire\PortfolioShow::class)->name('portfolios.show');
});

require __DIR__.'/auth.php';
