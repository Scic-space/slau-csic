<?php

namespace App\Models;

use App\Events\MemberApproved;
use App\Events\MemberRejected;
use App\Events\MemberSuspended;
use App\Notifications\EmailVerificationCodeNotification;
use App\Notifications\MemberSuspendedNotification;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Impersonate, Notifiable;

    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_code',
        'email_verification_code_expires_at',
        'member_number',
        'registration_number',
        'phone',
        'program',
        'faculty',
        'year_of_study',
        'intake',
        'intake_year',
        'date_of_birth',
        'gender',
        'residence',
        'membership_type',
        'membership_status',
        'is_discord_member',
        'discord_username',
        'joined_at',
        'bio',
        'headline',
        'specialization_track',
        'notable_problems_solved',
        'achievements_summary',
        'competition_rank',
        'emergency_contact_name',
        'emergency_contact_phone',
        'github_username',
        'linkedin_url',
        'personal_website',
        'portfolio_slug',
        'portfolio_is_public',
        'htb_profile_url',
        'htb_username',
        'htb_profile_data',
        'htb_last_synced_at',
        'profile_photo',
        'privacy_settings',
        'approval_notes',
        'admin_notes',
        'approved_by',
        'approved_at',
        'membership_expires_at',
        'membership_renewed_at',
        'suspension_reason',
        'suspended_until',
        'suspended_by',
        'attendance_count',
        'total_sessions_attended',
        'current_streak',
        'longest_streak',
        'bonus_points',
        'score',
        'rank',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'email_verification_code',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'email_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'joined_at' => 'date',
            'date_of_birth' => 'date',
            'is_discord_member' => 'boolean',
            'privacy_settings' => 'array',
            'htb_profile_data' => 'array',
            'htb_last_synced_at' => 'datetime',
            'approved_at' => 'datetime',
            'membership_expires_at' => 'date',
            'membership_renewed_at' => 'datetime',
            'suspended_until' => 'datetime',
            'rank_changed_at' => 'datetime',
            'intake_year' => 'integer',
        ];
    }

    protected static $logAttributes = ['name', 'email'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'membership_status', 'membership_type'])
            ->logOnlyDirty();
    }

    public function generateEmailVerificationCode(int $ttlMinutes = 15): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->forceFill([
            'email_verification_code' => Hash::make($code),
            'email_verification_code_expires_at' => now()->addMinutes($ttlMinutes),
        ])->save();

        return $code;
    }

    public function emailVerificationCodeIsValid(string $code): bool
    {
        if ($this->email_verification_code === null || $this->email_verification_code_expires_at === null) {
            return false;
        }

        if ($this->email_verification_code_expires_at->isPast()) {
            return false;
        }

        return Hash::check($code, $this->email_verification_code);
    }

    public function clearEmailVerificationCode(): void
    {
        $this->forceFill([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $code = $this->generateEmailVerificationCode();

        $this->notify(new EmailVerificationCodeNotification($code));
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('super-admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'Treasurer', 'President']);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'created_by');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function instructedEvents()
    {
        return $this->hasMany(Event::class, 'instructor_id');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_registrations')
            ->withPivot(['status', 'registered_at', 'attended_at'])
            ->withTimestamps();
    }

    public function eventAttendance()
    {
        return $this->hasMany(EventAttendance::class, 'member_id');
    }

    public function attendedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_attendance', 'member_id')
            ->withPivot(['status', 'checked_in_at'])
            ->withTimestamps();
    }

    public function favoriteEvents()
    {
        return $this->belongsToMany(Event::class, 'event_favorites')->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'lead_id');
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class);
    }

    public function socialLinks(): HasOne
    {
        return $this->hasOne(SocialLink::class);
    }

    public function privacy(): HasOne
    {
        return $this->hasOne(UserPrivacy::class);
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function actions(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function gamificationStats(): HasOne
    {
        return $this->hasOne(GamificationStat::class);
    }

    public function projectMemberships()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function memberProjects()
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot(['role', 'joined_at', 'left_at', 'contribution'])
            ->withTimestamps();
    }

    public function competitionParticipations()
    {
        return $this->hasMany(CompetitionParticipants::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_participants')
            ->withPivot(['team_name', 'role'])
            ->withTimestamps();
    }

    public function electionVotes(): HasMany
    {
        return $this->hasMany(ElectionVote::class);
    }

    public function electionNominations(): HasMany
    {
        return $this->hasMany(ElectionNomination::class);
    }

    public function hasVotedIn(Election $election): bool
    {
        return $this->electionVotes()
            ->where('election_id', $election->id)
            ->exists();
    }

    public function canVoteIn(Election $election): bool
    {
        if (! $election->isOpen()) {
            return false;
        }

        if ($election->is_test_ballot) {
            return $this->hasAnyRole(['admin', 'super-admin']);
        }

        if ($election->hasEligibilityOverrideFor($this)) {
            return $election->isExplicitlyEligible($this);
        }

        return $this->isActiveMember()
            && $this->hasPermissionTo('vote_in_elections');
    }

    public function hasNominatedFor(Election $election): bool
    {
        return $this->electionNominations()
            ->where('election_id', $election->id)
            ->exists();
    }

    public function clubResourceProgress(): HasMany
    {
        return $this->hasMany(ClubResourceProgress::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'instructor_id');
    }

    public function trainingEnrollments()
    {
        return $this->hasMany(TrainingEnrollment::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActiveMembers($query)
    {
        return $query->where('membership_status', 'active')
            ->where('membership_type', 'active');
    }

    public function scopeAssociateMembers($query)
    {
        return $query->where('membership_type', 'associate');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('membership_status', 'pending');
    }

    public function scopeExecutiveBoard($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', [
                'President',
                'Vice President',
                'General Secretary',
                'Treasurer',
                'Head of Projects',
                'CTF Lead',
                'Public Relations',
                'Lead Developer',
                'Technical Lead',
            ]);
        });
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    public function isActiveMember(): bool
    {
        return $this->membership_status === 'active'
            && $this->membership_type === 'active';
    }

    public function isExecutiveMember(): bool
    {
        return $this->hasAnyRole([
            'admin',
            'President',
            'Vice President',
            'General Secretary',
            'Treasurer',
            'Head of Projects',
            'CTF Lead',
            'Public Relations',
            'Lead Developer',
            'Technical Lead',
        ]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function canVote(): bool
    {
        return $this->isActiveMember() && $this->hasPermissionTo('vote_in_elections');
    }

    public function getAttendanceRate(): float
    {
        $totalMeetings = Meeting::where('scheduled_at', '<=', now())
            ->where('created_at', '>=', Carbon::parse($this->joined_at))
            ->count();

        if ($totalMeetings === 0) {
            return 0;
        }

        $attendedMeetings = $this->attendance()->count();

        return round(($attendedMeetings / $totalMeetings) * 100, 2);
    }

    public function hasAttendedMeeting(Meeting $meeting): bool
    {
        return $this->attendance()
            ->where('meeting_id', $meeting->id)
            ->exists();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->email;
    }

    public function getRoleNamesAttribute(): string
    {
        return $this->roles->pluck('name')->map(function (string $role): string {
            return Str::headline($role);
        })->join(', ');
    }

    // In User model
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo) {
            if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
                return $this->profile_photo;
            }

            if (Storage::disk('public')->exists($this->profile_photo)) {
                return url('storage/'.$this->profile_photo);
            }
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=FFFFFF&background=6366f1&bold=true';
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->avatar_url;
    }

    public function getAvatarInitialsAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 2));
    }

    // ============================================
    // ATTENDANCE METHODS
    // ============================================

    public function updateAttendanceCount()
    {
        $this->update([
            'attendance_count' => $this->attendance()->count(),
        ]);
    }

    public function meetingsThisSemester()
    {
        // Assuming semester starts in September or February
        $semesterStart = now()->month >= 9
            ? now()->setMonth(9)->startOfMonth()
            : now()->setMonth(2)->startOfMonth();

        return $this->attendance()
            ->whereHas('meeting', function ($query) use ($semesterStart) {
                $query->where('scheduled_at', '>=', $semesterStart);
            })
            ->count();
    }

    public function isActiveThisSemester(): bool
    {
        // Active = attended at least 2 meetings this semester (per constitution)
        return $this->meetingsThisSemester() >= 2;
    }

    // ============================================
    // MEMBER MANAGEMENT METHODS
    // ============================================

    // Relationships for approval workflow
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    public function suspendedByUser()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function suspendedUsers()
    {
        return $this->hasMany(User::class, 'suspended_by');
    }

    // Privacy Settings Methods
    public function getPrivacySettingsAttribute($value): array
    {
        $knownKeys = ['show_email', 'show_phone', 'show_discord', 'show_attendance', 'show_program', 'show_year', 'allow_contact', 'show_profile'];

        $defaults = [
            'show_email' => false,
            'show_phone' => false,
            'show_discord' => false,
            'show_attendance' => false,
            'show_program' => true,
            'show_year' => true,
            'allow_contact' => true,
            'show_profile' => true,
        ];

        $stored = $value ? json_decode($value, true) : [];

        $stored = array_intersect_key($stored, array_flip($knownKeys));

        return array_merge($defaults, $stored);
    }

    public function setPrivacySettingsAttribute(array|string|null $value): void
    {
        if (is_array($value) && array_is_list($value)) {
            $keys = ['show_email', 'show_phone', 'show_discord', 'show_attendance', 'show_program', 'show_year', 'allow_contact', 'show_profile'];
            $normalized = [];
            foreach ($keys as $key) {
                $normalized[$key] = in_array($key, $value, true);
            }
            $this->attributes['privacy_settings'] = json_encode($normalized);

            return;
        }

        $this->attributes['privacy_settings'] = is_array($value) ? json_encode($value) : $value;
    }

    public function canShowField(string $field): bool
    {
        $showProfile = (bool) ($this->privacy_settings['show_profile'] ?? true);

        if ($field === 'show_profile') {
            return $showProfile;
        }

        return (bool) ($this->privacy_settings[$field] ?? false) && $showProfile;
    }

    public function canBeContacted(): bool
    {
        return ($this->privacy_settings['allow_contact'] ?? true) && $this->show_profile;
    }

    // Approval Workflow Methods
    public function isPendingApproval(): bool
    {
        return $this->membership_status === 'pending' && is_null($this->approved_at);
    }

    public function isApproved(): bool
    {
        return ! is_null($this->approved_at) && $this->membership_status !== 'inactive';
    }

    public function isInactive(): bool
    {
        return $this->membership_status === 'inactive';
    }

    public function assignMemberNumber(): void
    {
        if ($this->member_number !== null) {
            return;
        }

        $this->update([
            'member_number' => (int) self::query()->max('member_number') + 1,
        ]);
    }

    public function approve(?User $approver = null, ?string $notes = null): bool
    {
        try {
            $this->update([
                'membership_status' => 'active',
                'approved_at' => now(),
                'approved_by' => $approver?->id,
                'approval_notes' => $notes,
                'membership_expires_at' => $this->membershipExpiryDate(),
            ]);

            $this->assignMemberNumber();

            $this->membership?->update([
                'status' => 'active',
                'approved_by' => $approver?->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->assignRole('member');

            $this->notify(new \App\Notifications\MemberApprovalNotification($approver, $notes));

            MemberApproved::dispatch($this, $this->membership, $approver ?? $this);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function reject(?User $rejecter = null, ?string $notes = null): bool
    {
        try {
            $this->update([
                'membership_status' => 'inactive',
                'approved_by' => $rejecter?->id,
                'approval_notes' => $notes,
            ]);

            $this->membership?->update([
                'status' => 'rejected',
                'approved_by' => $rejecter?->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $this->notify(new \App\Notifications\MemberRejectionNotification($rejecter, $notes));

            MemberRejected::dispatch($this, $this->membership, $rejecter ?? $this);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function suspend(string $reason, ?User $suspender = null, ?\DateTime $until = null): bool
    {
        try {
            $this->update([
                'membership_status' => 'suspended',
                'suspension_reason' => $reason,
                'suspended_until' => $until,
                'suspended_by' => $suspender?->id,
            ]);

            $this->membership?->update([
                'status' => 'suspended',
                'suspension_reason' => $reason,
                'suspended_until' => $until,
                'suspended_by' => $suspender?->id,
            ]);

            $this->notify(new MemberSuspendedNotification($reason, $until));

            MemberSuspended::dispatch($this, $this->membership, $suspender ?? $this);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Auto-alumni detection
    public function shouldBeAlumni(): bool
    {
        if ($this->membership_type === 'alumni') {
            return false;
        }

        if (! $this->hasGraduationAnchor()) {
            return false;
        }

        // If current year is past expected graduation, mark as alumni
        return now()->year > $this->graduationYear();
    }

    public function convertToAlumni(): bool
    {
        try {
            $this->update([
                'membership_type' => 'alumni',
                'membership_status' => 'active',
            ]);

            $this->membership?->update([
                'type' => 'alumni',
                'status' => 'active',
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function leave(): bool
    {
        try {
            $this->update([
                'membership_status' => 'inactive',
            ]);

            $this->membership?->update([
                'status' => 'left',
                'left_at' => now(),
            ]);

            activity()
                ->performedOn($this)
                ->causedBy($this)
                ->log("Member {$this->name} voluntarily left the club");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function courseDurationYears(): int
    {
        $program = $this->program ?? $this->memberProfile?->program;

        return AcademicLevel::fromProgram((string) $program)->durationYears();
    }

    public function graduationYear(): int
    {
        // Preferred: expiry is anchored to the admission year on a course of the
        // student's programme level (e.g. Bachelor = 3 years, Diploma = 2 years).
        if ($this->intake_year) {
            return $this->intake_year + $this->courseDurationYears();
        }

        $yearOfStudy = $this->year_of_study ?? $this->memberProfile?->year_of_study;

        if (! $yearOfStudy) {
            return now()->year;
        }

        return now()->year + ($this->courseDurationYears() - $yearOfStudy);
    }

    public function studyExpiryDate(): ?Carbon
    {
        if ($this->membership_type === 'alumni') {
            return null;
        }

        if (! $this->hasGraduationAnchor()) {
            return null;
        }

        $graduationYear = $this->graduationYear();

        return match ($this->intake) {
            'january' => Carbon::createFromDate($graduationYear, 1, 31),
            'may' => Carbon::createFromDate($graduationYear, 5, 31),
            'august' => Carbon::createFromDate($graduationYear, 8, 31),
            default => Carbon::createFromDate($graduationYear, 12, 31),
        };
    }

    protected function hasGraduationAnchor(): bool
    {
        return (bool) $this->intake_year
            || (bool) ($this->year_of_study ?? $this->memberProfile?->year_of_study);
    }

    public function membershipExpiryDate(): ?Carbon
    {
        return $this->membership_expires_at ?? $this->studyExpiryDate();
    }

    public function renew(int $durationMonths = 12): bool
    {
        try {
            $this->update([
                'membership_status' => 'active',
                'membership_expires_at' => now()->addMonths($durationMonths),
                'membership_renewed_at' => now(),
            ]);

            activity()
                ->performedOn($this)
                ->causedBy($this)
                ->log("Member {$this->name} renewed their membership");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Member Statistics Methods
    public function getMemberStats(): array
    {
        $competitionParticipations = $this->competitionParticipations()->count();
        $portalProgress = $this->clubResourceProgress()->get();

        return [
            'total_attendance' => $this->attendance()->count(),
            'attendance_rate' => $this->getAttendanceRate(),
            'events_attended' => $this->eventRegistrations()->where('status', 'attended')->count(),
            'projects_led' => $this->projects()->count(),
            'projects_participated' => $this->projectMemberships()->count(),
            'competition_entries' => $competitionParticipations,
            'latest_competition_rank' => $this->competition_rank,
            'club_portal_active_tracks' => $portalProgress->where('status', 'in_progress')->count(),
            'club_portal_completed_tracks' => $portalProgress->where('status', 'completed')->count(),
            'club_portal_average_progress' => (int) round($portalProgress->avg('progress_percentage') ?? 0),
            'trainings_completed' => $this->trainingEnrollments()->where('status', 'completed')->count(),
            'meetings_this_semester' => $this->meetingsThisSemester(),
            'is_active_this_semester' => $this->isActiveThisSemester(),
            'membership_duration' => $this->joined_at ? (int) abs(now()->diffInMonths($this->joined_at)) : 0,
        ];
    }

    // Scopes for member management
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at')
            ->where('membership_status', '!=', 'inactive');
    }

    public function scopeInactive($query)
    {
        return $query->where('membership_status', 'inactive');
    }

    public function scopeSuspended($query)
    {
        return $query->where('membership_status', 'suspended');
    }

    public function scopeAlumni($query)
    {
        return $query->where('membership_type', 'alumni');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('membership_status', 'active')
            ->whereDate('membership_expires_at', '<=', now()->addDays($days))
            ->whereDate('membership_expires_at', '>', now());
    }

    public function scopePubliclyVisible($query)
    {
        return $query->approved()
            ->where('membership_status', 'active')
            ->where(function ($q) {
                $q->where('privacy_settings->show_profile', true)
                    ->orWhereNull('privacy_settings->show_profile');
            });
    }

    // In app/Models/User.php
    public function scopeForDirectory($query)
    {
        return $query->where(function ($q) {
            // Include publicly visible users
            $q->where(function ($inner) {
                $inner->approved()
                    ->where('membership_status', 'active')
                    ->where(function ($privacy) {
                        $privacy->where('privacy_settings->show_profile', true)
                            ->orWhereNull('privacy_settings->show_profile');
                    });
            });

            // Also include admin users regardless of settings
            // ->orWhere(function($inner) {
            //     $inner->where('is_admin', true)
            //         ->orWhereHas('roles', function($roleQuery) {
            //             $roleQuery->where('name', 'admin');
            //         });
            // });
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function finePayments()
    {
        return $this->hasMany(FinePayment::class, 'recorded_by');
    }

    public function issuedFines()
    {
        return $this->hasMany(Fine::class, 'issued_by');
    }

    public function waivedFines()
    {
        return $this->hasMany(Fine::class, 'waived_by');
    }

    public function fineAppeals()
    {
        return $this->hasMany(FineAppeal::class, 'reviewed_by');
    }

    public function ctfSubmissions(): HasMany
    {
        return $this->hasMany(CtfSubmission::class);
    }

    // Accessors for public display
    public function getShowEmailAttribute(): bool
    {
        return $this->canShowField('show_email');
    }

    public function getShowPhoneAttribute(): bool
    {
        return $this->canShowField('show_phone');
    }

    public function getShowDiscordAttribute(): bool
    {
        return $this->canShowField('show_discord');
    }

    public function getShowAttendanceAttribute(): bool
    {
        return $this->canShowField('show_attendance');
    }

    public function getShowProgramAttribute(): bool
    {
        return $this->canShowField('show_program');
    }

    public function getShowYearAttribute(): bool
    {
        return $this->canShowField('show_year');
    }

    public function getShowProfileAttribute(): bool
    {
        return $this->canShowField('show_profile');
    }

    public function getCanBeContactedAttribute(): bool
    {
        return $this->canBeContacted();
    }

    public function shouldNotify(string $type): bool
    {
        $preferences = $this->notificationPreferences;

        if (! $preferences) {
            return true;
        }

        return match ($type) {
            'event_reminders' => $preferences->event_reminders,
            'event_cancellations' => $preferences->event_cancellations,
            'challenge_solved' => $preferences->challenge_solved,
            'membership_updates' => $preferences->membership_updates,
            'broadcast_messages' => $preferences->broadcast_messages,
            'fine_notifications' => $preferences->fine_notifications,
            'weekly_digest' => $preferences->weekly_digest,
            default => true,
        };
    }

    // ============================================
    // LEADERBOARD HELPERS
    // ============================================

    public function getTeachingSessionAttendanceRate(): float
    {
        $totalSessions = Meeting::teachingSessions()
            ->completedTeachingSessions()
            ->count();

        if ($totalSessions === 0) {
            return 0;
        }

        $attendedSessions = $this->attendance()
            ->whereHas('meeting', function ($query) {
                $query->teachingSessions()
                    ->completedTeachingSessions();
            })
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }

    public function getTeachingSessionsAttended(): int
    {
        return $this->attendance()
            ->whereHas('meeting', function ($query) {
                $query->teachingSessions()
                    ->completedTeachingSessions();
            })
            ->whereIn('status', ['present', 'late'])
            ->count();
    }

    public function getTotalTeachingSessions(): int
    {
        return Meeting::teachingSessions()
            ->completedTeachingSessions()
            ->count();
    }

    public function calculateScore(): float
    {
        $attendanceRate = $this->getTeachingSessionAttendanceRate();
        $consistencyScore = min($this->current_streak * 10, 100);
        $bonusPoints = $this->bonus_points ?? 0;

        return ($attendanceRate * 0.7) + ($consistencyScore * 0.2) + ($bonusPoints * 0.1);
    }

    public function isEligible(): bool
    {
        $attendanceRate = $this->getTeachingSessionAttendanceRate();
        $sessionsAttended = $this->getTeachingSessionsAttended();

        return $attendanceRate >= 75 && $sessionsAttended >= 5;
    }

    public function getAttendanceRank(): int
    {
        return User::where('score', '>', $this->score)->count() + 1;
    }

    // ============================================
    // GAMIFICATION METHODS
    // ============================================

    /**
     * Rank thresholds for automatic rank upgrades.
     */
    public const RANK_THRESHOLDS = [
        'bronze' => 0,
        'silver' => 200,
        'gold' => 500,
        'platinum' => 1000,
    ];

    /**
     * Get the user's point transactions.
     */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function challengeSubmissions(): HasMany
    {
        return $this->hasMany(ChallengeSubmission::class);
    }

    /**
     * Get badges earned by this user.
     */
    public function earnedBadges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Get the total points earned by this user.
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->pointTransactions()->sum('points') ?? 0;
    }

    /**
     * Get the current rank based on total points.
     */
    public function getCurrentRankAttribute(): string
    {
        $points = $this->total_points;

        if ($points >= self::RANK_THRESHOLDS['platinum']) {
            return 'platinum';
        }
        if ($points >= self::RANK_THRESHOLDS['gold']) {
            return 'gold';
        }
        if ($points >= self::RANK_THRESHOLDS['silver']) {
            return 'silver';
        }

        return 'bronze';
    }

    /**
     * Sync the user's rank based on their current total points.
     */
    public function syncRank(): void
    {
        $newRank = $this->current_rank;

        if ($this->rank !== $newRank) {
            $this->update([
                'rank' => $newRank,
                'rank_changed_at' => now(),
            ]);
        }
    }

    public function portfolioSkills(): HasMany
    {
        return $this->hasMany(PortfolioSkill::class)->orderBy('sort_order');
    }

    public function portfolioCertifications(): HasMany
    {
        return $this->hasMany(PortfolioCertification::class)->orderByDesc('date_earned');
    }

    public function portfolioExperiences(): HasMany
    {
        return $this->hasMany(PortfolioExperience::class)->orderByDesc('start_date');
    }

    public function portfolioEntries(): HasMany
    {
        return $this->hasMany(StudentPortfolio::class, 'student_id')->where('is_published', true)->orderByDesc('created_at');
    }
}
