<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EventAnalytics;
use App\Filament\Pages\MeetingAnalytics;
use App\Filament\Pages\MyProfile;
use App\Filament\Pages\SystemOverview;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Attendance\EventAttendanceResource;
use App\Filament\Resources\Attendance\MeetingAttendanceResource;
use App\Filament\Resources\Badges\BadgeResource;
use App\Filament\Resources\BudgetCategories\BudgetCategoryResource;
use App\Filament\Resources\CtfCategories\CtfCategoryResource;
use App\Filament\Resources\CtfCompetitions\CtfCompetitionResource;
use App\Filament\Resources\CtfSubmissions\CtfSubmissionResource;
use App\Filament\Resources\CtfWriteups\CtfWriteupResource;
use App\Filament\Resources\Elections\ElectionResource as ElectionFilamentResource;
use App\Filament\Resources\EventCategories\EventCategoryResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Resources\FineAppeals\FineAppealResource;
use App\Filament\Resources\Fines\FineResource;
use App\Filament\Resources\FineTypes\FineTypeResource;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Registrations\EventRegistrationResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\RoleTemplates\RoleTemplateResource;
use App\Filament\Resources\System\AuditLogResource;
use App\Filament\Resources\System\ContentPageResource;
use App\Filament\Resources\System\SettingsResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\CtfCompetition;
use App\Models\Election;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\Fine;
use App\Models\FineAppeal;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->groups([
                        NavigationGroup::make()
                            ->items([
                                NavigationItem::make('Dashboard')
                                    ->icon('heroicon-o-home')
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                                    ->url(fn (): string => Dashboard::getUrl())
                                    ->sort(-2),
                                NavigationItem::make('My Profile')
                                    ->icon('heroicon-o-user')
                                    ->sort(-1)
                                    ->url(fn (): string => MyProfile::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(MyProfile::getRouteName())),
                            ]),
                        NavigationGroup::make('Membership')
                            ->items([
                                NavigationItem::make('Users')
                                    ->icon('heroicon-o-users')
                                    ->group('Membership')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('view_users') ?? false)
                                    ->url(fn (): string => UserResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(UserResource::getRouteBaseName().'*')),
                                NavigationItem::make('Pending Approvals')
                                    ->icon('heroicon-o-clock')
                                    ->group('Membership')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('approve_members') ?? false)
                                    ->badge(fn (): string => (string) User::where('membership_status', 'pending')->count(), 'warning')
                                    ->url(fn (): string => UserResource::getUrl('index', ['tab' => 'pending']))
                                    ->isActiveWhen(fn (): bool => request()->routeIs(UserResource::getRouteBaseName().'*')),
                                NavigationItem::make('Alumni')
                                    ->icon('heroicon-o-academic-cap')
                                    ->group('Membership')
                                    ->sort(4)
                                    ->visible(fn (): bool => auth()->user()?->can('edit_users') ?? false)
                                    ->url(fn (): string => UserResource::getUrl('index', ['tab' => 'alumni']))
                                    ->isActiveWhen(fn (): bool => request()->routeIs(UserResource::getRouteBaseName().'*')),
                                NavigationItem::make('Badges')
                                    ->icon('heroicon-o-star')
                                    ->group('Membership')
                                    ->sort(5)
                                    ->visible(fn (): bool => auth()->user()?->can('viewAny', \App\Models\Badge::class) ?? false)
                                    ->url(fn (): string => BadgeResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(BadgeResource::getRouteBaseName().'*')),
                            ]),
                        NavigationGroup::make('Events')
                            ->items([
                                NavigationItem::make('Events')
                                    ->icon('heroicon-o-calendar-days')
                                    ->group('Events')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('view_events') ?? false)
                                    ->badge(fn (): string => (string) Event::where('start_date', '>=', now())->where('status', '!=', 'cancelled')->count(), 'info')
                                    ->url(fn (): string => EventResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(EventResource::getRouteBaseName().'*')),
                                NavigationItem::make('Calendar')
                                    ->icon('heroicon-o-calendar')
                                    ->group('Events')
                                    ->sort(0)
                                    ->visible(fn (): bool => auth()->user()?->can('view_events') ?? false)
                                    ->url(fn (): string => \App\Filament\Pages\EventCalendar::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(\App\Filament\Pages\EventCalendar::getRouteName())),
                                NavigationItem::make('Categories')
                                    ->icon('heroicon-o-tag')
                                    ->group('Events')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('edit_events') ?? false)
                                    ->url(fn (): string => EventCategoryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(EventCategoryResource::getRouteBaseName().'*')),
                                NavigationItem::make('Registrations')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->group('Events')
                                    ->sort(3)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_registrations') ?? false)
                                    ->badge(fn (): string => (string) EventRegistration::where('status', 'registered')->count(), 'warning')
                                    ->url(fn (): string => EventRegistrationResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(EventRegistrationResource::getRouteBaseName().'*')),
                                NavigationItem::make('Attendance')
                                    ->icon('heroicon-o-check-circle')
                                    ->group('Events')
                                    ->sort(4)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_attendance') ?? false)
                                    ->badge(fn (): string => (string) EventAttendance::where('status', 'present')->whereDate('checked_in_at', now()->toDateString())->count(), 'success')
                                    ->url(fn (): string => EventAttendanceResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(EventAttendanceResource::getRouteBaseName().'*')),
                                NavigationItem::make('Analytics')
                                    ->icon('heroicon-o-chart-bar')
                                    ->group('Events')
                                    ->sort(5)
                                    ->visible(fn (): bool => auth()->user()?->can('view_events') ?? false)
                                    ->url(fn (): string => EventAnalytics::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(EventAnalytics::getRouteName())),
                            ]),
                        NavigationGroup::make('Meetings')
                            ->items([
                                NavigationItem::make('Meetings')
                                    ->icon('heroicon-o-video-camera')
                                    ->group('Meetings')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('view_meetings') ?? false)
                                    ->url(fn (): string => MeetingResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(MeetingResource::getRouteBaseName().'*')),
                                NavigationItem::make('Attendance')
                                    ->icon('heroicon-o-check-circle')
                                    ->group('Meetings')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('view_attendance') ?? false)
                                    ->badge(fn (): string => (string) \App\Models\Attendance::where('status', 'present')->whereDate('checked_in_at', now()->toDateString())->count(), 'success')
                                    ->url(fn (): string => MeetingAttendanceResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(MeetingAttendanceResource::getRouteBaseName().'*')),
                                NavigationItem::make('Analytics')
                                    ->icon('heroicon-o-chart-bar')
                                    ->group('Meetings')
                                    ->sort(3)
                                    ->visible(fn (): bool => auth()->user()?->can('view_attendance') ?? false)
                                    ->url(fn (): string => MeetingAnalytics::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(MeetingAnalytics::getRouteName())),
                            ]),
                        NavigationGroup::make('Finance')
                            ->items([
                                NavigationItem::make('Transactions')
                                    ->icon('heroicon-o-banknotes')
                                    ->group('Finance')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('view_budget') ?? false)
                                    ->url(fn (): string => TransactionResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(TransactionResource::getRouteBaseName().'*')),
                                NavigationItem::make('Budget Categories')
                                    ->icon('heroicon-o-chart-pie')
                                    ->group('Finance')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_budget') ?? false)
                                    ->url(fn (): string => BudgetCategoryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(BudgetCategoryResource::getRouteBaseName().'*')),
                            ]),
                        NavigationGroup::make('Fines')
                            ->items([
                                NavigationItem::make('Fines')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->group('Fines')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('viewAny', Fine::class) ?? false)
                                    ->badge(fn (): string => (string) Fine::whereIn('status', ['pending', 'partially_paid'])->count(), 'danger')
                                    ->url(fn (): string => FineResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(FineResource::getRouteBaseName().'*')),
                                NavigationItem::make('Fine Types')
                                    ->icon('heroicon-o-scale')
                                    ->group('Fines')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('viewAny', FineType::class) ?? false)
                                    ->url(fn (): string => FineTypeResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(FineTypeResource::getRouteBaseName().'*')),
                                NavigationItem::make('Appeals')
                                    ->icon('heroicon-o-arrow-path')
                                    ->group('Fines')
                                    ->sort(3)
                                    ->visible(fn (): bool => auth()->user()?->can('viewAny', FineAppeal::class) ?? false)
                                    ->badge(fn (): string => (string) FineAppeal::where('status', 'pending')->count(), 'warning')
                                    ->url(fn (): string => FineAppealResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(FineAppealResource::getRouteBaseName().'*')),
                            ]),

                        NavigationGroup::make('Exams')
                            ->items([
                                NavigationItem::make('Exams')
                                    ->icon('heroicon-o-document-text')
                                    ->group('Exams')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => ExamResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(ExamResource::getRouteBaseName().'*')),
                            ]),
                        NavigationGroup::make('CTF')
                            ->items([
                                NavigationItem::make('CTF Dashboard')
                                    ->icon('heroicon-o-flag')
                                    ->group('CTF')
                                    ->sort(0)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false)
                                    ->url(fn (): string => \App\Filament\Pages\CtfDashboard::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(\App\Filament\Pages\CtfDashboard::getRouteName())),
                                NavigationItem::make('CTF Competitions')
                                    ->icon('heroicon-o-flag')
                                    ->group('CTF')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_competition_teams') ?? false)
                                    ->badge(fn (): string => (string) CtfCompetition::where('status', 'published')->count(), 'success')
                                    ->url(fn (): string => CtfCompetitionResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(CtfCompetitionResource::getRouteBaseName().'*')),
                                NavigationItem::make('Categories')
                                    ->icon('heroicon-o-tag')
                                    ->group('CTF')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_competition_teams') ?? false)
                                    ->url(fn (): string => CtfCategoryResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(CtfCategoryResource::getRouteBaseName().'*')),
                                NavigationItem::make('Submissions')
                                    ->icon('heroicon-o-arrow-up-circle')
                                    ->group('CTF')
                                    ->sort(3)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_competition_teams') ?? false)
                                    ->url(fn (): string => CtfSubmissionResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(CtfSubmissionResource::getRouteBaseName().'*')),
                                NavigationItem::make('Writeups')
                                    ->icon('heroicon-o-document-text')
                                    ->group('CTF')
                                    ->sort(4)
                                    ->visible(fn (): bool => auth()->user()?->can('manage_competition_teams') ?? false)
                                    ->url(fn (): string => CtfWriteupResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(CtfWriteupResource::getRouteBaseName().'*')),
                            ]),
                        NavigationGroup::make('Testimonials')
                            ->items([
                                NavigationItem::make('Testimonials')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->group('Testimonials')
                                    ->sort(0)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin', 'CTF Lead']) ?? false)
                                    ->badge(fn (): string => (string) \App\Models\Testimonial::where('is_approved', false)->count(), 'warning')
                                    ->url(fn (): string => \App\Filament\Resources\Testimonials\TestimonialResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(\App\Filament\Resources\Testimonials\TestimonialResource::getRouteBaseName().'*')),
                                NavigationItem::make('Contact Messages')
                                    ->icon('heroicon-o-envelope')
                                    ->group('Testimonials')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->badge(fn (): string => (string) \App\Models\ContactMessage::unread()->count(), 'warning')
                                    ->url(fn (): string => \App\Filament\Resources\ContactMessages\ContactMessageResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(\App\Filament\Resources\ContactMessages\ContactMessageResource::getRouteBaseName().'*')),
                            ]),
                        NavigationGroup::make('Elections')
                            ->items([
                                NavigationItem::make('Elections')
                                    ->icon('heroicon-o-check-badge')
                                    ->group('Elections')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin', 'President']) ?? false)
                                    ->badge(fn (): string => (string) Election::where('status', 'open')->count(), 'success')
                                    ->url(fn (): string => ElectionFilamentResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(ElectionFilamentResource::getRouteBaseName().'*')),
                            ]),

                        NavigationGroup::make('Assignments')
                            ->items([
                                NavigationItem::make('Role Templates')
                                    ->icon('heroicon-o-academic-cap')
                                    ->group('Assignments')
                                    ->sort(2)
                                    ->visible(fn (): bool => (auth()->user()?->can('view_assignments') || auth()->user()?->hasAnyRole(['admin', 'super-admin'])) ?? false)
                                    ->url(fn (): string => RoleTemplateResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(RoleTemplateResource::getRouteBaseName().'*')),
                            ]),

                        NavigationGroup::make('Projects')
                            ->items([
                                NavigationItem::make('Projects')
                                    ->icon('heroicon-o-code-bracket-square')
                                    ->group('Projects')
                                    ->sort(0)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin', 'Head of Projects']) ?? false)
                                    ->url(fn (): string => \App\Filament\Resources\Projects\ProjectResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(\App\Filament\Resources\Projects\ProjectResource::getRouteBaseName().'*')),
                            ]),

                        NavigationGroup::make('System')
                            ->items([
                                NavigationItem::make('System Overview')
                                    ->icon('heroicon-o-server-stack')
                                    ->group('System')
                                    ->sort(0)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => SystemOverview::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(SystemOverview::getRouteName())),
                                NavigationItem::make('Roles & Permissions')
                                    ->icon('heroicon-o-shield-check')
                                    ->group('System')
                                    ->sort(1)
                                    ->visible(fn (): bool => auth()->user()?->hasRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => RoleResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(RoleResource::getRouteBaseName().'*')),
                                NavigationItem::make('Announcements')
                                    ->icon('heroicon-o-megaphone')
                                    ->group('System')
                                    ->sort(2)
                                    ->visible(fn (): bool => auth()->user()?->can('send_announcements') ?? false)
                                    ->url(fn (): string => AnnouncementResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(AnnouncementResource::getRouteBaseName().'*')),
                                NavigationItem::make('News')
                                    ->icon('heroicon-o-newspaper')
                                    ->group('System')
                                    ->sort(3)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => NewsResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(NewsResource::getRouteBaseName().'*')),
                                NavigationItem::make('Settings')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->group('System')
                                    ->sort(4)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => SettingsResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(SettingsResource::getRouteBaseName().'*')),

                                NavigationItem::make('Content Pages')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->group('System')
                                    ->sort(5)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => ContentPageResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(ContentPageResource::getRouteBaseName().'*')),
                                NavigationItem::make('Audit Log')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->group('System')
                                    ->sort(7)
                                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false)
                                    ->url(fn (): string => AuditLogResource::getUrl())
                                    ->isActiveWhen(fn (): bool => request()->routeIs(AuditLogResource::getRouteBaseName().'*')),
                            ]),
                    ]);
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
