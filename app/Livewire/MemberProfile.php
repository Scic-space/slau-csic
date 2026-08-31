<?php

namespace App\Livewire;

use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Profile')]
class MemberProfile extends Component
{
    use WithFileUploads;

    public $name;

    public $email;

    public $registration_number;

    public $phone;

    public $program;

    public $faculty;

    public $year_of_study;

    public $intake;

    public $intake_year;

    public $bio;

    public $headline;

    public $github_username;

    public $linkedin_url;

    public $discord_username;

    public $is_discord_member;

    public $profile_photo;

    public string $profilePhotoUrl = '';

    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    public $show_email;

    public $show_phone;

    public $show_discord;

    public $show_attendance;

    public $show_program;

    public $show_year;

    public $show_profile;

    public $notify_event_reminders;

    public $notify_event_cancellations;

    public $notify_challenge_solved;

    public $notify_membership_updates;

    public $notify_broadcast_messages;

    public $notify_fine_notifications;

    public $notify_weekly_digest;

    public $confirmingDelete = false;

    public $delete_password;

    public $logout_password;

    public function mount(): void
    {
        $user = Auth::user()->load(['memberProfile', 'socialLinks', 'privacy', 'notificationPreferences']);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->registration_number = $user->registration_number;
        $this->phone = $user->memberProfile?->phone;
        $this->program = $user->memberProfile?->program;
        $this->faculty = $user->memberProfile?->faculty;
        $this->year_of_study = $user->memberProfile?->year_of_study;
        $this->intake = $user->intake;
        $this->intake_year = $user->intake_year;
        $this->bio = $user->memberProfile?->bio;
        $this->headline = $user->memberProfile?->headline;
        $this->github_username = $user->socialLinks?->github_username;
        $this->linkedin_url = $user->socialLinks?->linkedin_url;
        $this->discord_username = $user->socialLinks?->discord_username ?? $user->discord_username;
        $this->is_discord_member = $user->socialLinks?->is_discord_member ?? $user->is_discord_member;
        $this->profilePhotoUrl = $user->profile_photo_url;
        $this->show_email = $user->privacy?->show_email ?? true;
        $this->show_phone = $user->privacy?->show_phone ?? false;
        $this->show_discord = $user->privacy?->show_discord ?? false;
        $this->show_attendance = $user->privacy?->show_attendance ?? true;
        $this->show_program = $user->privacy?->show_program ?? true;
        $this->show_year = $user->privacy?->show_year ?? true;
        $this->show_profile = $user->privacy?->show_profile ?? true;
        $this->notify_event_reminders = $user->notificationPreferences?->event_reminders ?? true;
        $this->notify_event_cancellations = $user->notificationPreferences?->event_cancellations ?? true;
        $this->notify_challenge_solved = $user->notificationPreferences?->challenge_solved ?? true;
        $this->notify_membership_updates = $user->notificationPreferences?->membership_updates ?? true;
        $this->notify_broadcast_messages = $user->notificationPreferences?->broadcast_messages ?? true;
        $this->notify_fine_notifications = $user->notificationPreferences?->fine_notifications ?? true;
        $this->notify_weekly_digest = $user->notificationPreferences?->weekly_digest ?? false;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'registration_number' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'program' => ['nullable', 'string', 'max:255'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'year_of_study' => ['nullable', 'integer', 'min:1', 'max:6'],
            'intake' => ['nullable', 'string', 'in:january,february,may,august'],
            'intake_year' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            'bio' => ['nullable', 'string', 'max:1000'],
            'headline' => ['nullable', 'string', 'max:255'],
            'github_username' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'discord_username' => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'registration_number' => $validated['registration_number'] ?? null,
            'discord_username' => $validated['discord_username'] ?? null,
            'intake' => $validated['intake'] ?? null,
            'intake_year' => $validated['intake_year'] ?? null,
        ]);

        $user->save();

        $user->memberProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $validated['phone'] ?? null,
                'program' => $validated['program'] ?? null,
                'faculty' => $validated['faculty'] ?? null,
                'year_of_study' => $validated['year_of_study'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'headline' => $validated['headline'] ?? null,
            ]
        );

        $user->socialLinks()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'github_username' => $validated['github_username'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'discord_username' => $validated['discord_username'] ?? null,
            ]
        );

        $this->dispatch('toast-show', message: 'Profile updated successfully.', type: 'success');
    }

    public function updatedFaculty(): void
    {
        $this->program = null;
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password does not match our records.');

            return;
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset('current_password', 'new_password', 'new_password_confirmation');

        $this->dispatch('toast-show', message: 'Password updated successfully.', type: 'success');
    }

    public function updatePhoto(): void
    {
        $this->validate([
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($this->profile_photo) {
            $user = Auth::user();
            $profilePhoto = app(ImageOptimizer::class)->store($this->profile_photo, 'profile-photos', 800, 800, 84);

            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->profile_photo = $profilePhoto;
            $user->save();

            $this->profilePhotoUrl = $user->profile_photo_url.'?v='.now()->getTimestampMs();
            $this->reset('profile_photo');
            $this->dispatch('profile-photo-updated', url: $this->profilePhotoUrl);
            $this->dispatch('toast-show', message: 'Profile photo updated successfully.', type: 'success');
        }
    }

    public function updatedProfilePhoto(): void
    {
        $this->updatePhoto();
    }

    public function updatePrivacy(): void
    {
        $user = Auth::user();

        $user->privacy()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'show_email' => $this->show_email,
                'show_phone' => $this->show_phone,
                'show_discord' => $this->show_discord,
                'show_attendance' => $this->show_attendance,
                'show_program' => $this->show_program,
                'show_year' => $this->show_year,
                'show_profile' => $this->show_profile,
            ]
        );

        $this->dispatch('toast-show', message: 'Privacy settings updated.', type: 'success');
    }

    public function updateNotificationPreferences(): void
    {
        $user = Auth::user();

        $user->notificationPreferences()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'event_reminders' => $this->notify_event_reminders,
                'event_cancellations' => $this->notify_event_cancellations,
                'challenge_solved' => $this->notify_challenge_solved,
                'membership_updates' => $this->notify_membership_updates,
                'broadcast_messages' => $this->notify_broadcast_messages,
                'fine_notifications' => $this->notify_fine_notifications,
                'weekly_digest' => $this->notify_weekly_digest,
            ]
        );

        $this->dispatch('toast-show', message: 'Notification preferences updated.', type: 'success');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->reset('delete_password');
    }

    public function deleteAccount(): void
    {
        $this->validate([
            'delete_password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($this->delete_password, $user->password)) {
            $this->addError('delete_password', 'The password does not match our records.');

            return;
        }

        Auth::logout();

        $user->delete();

        $this->redirect('/');
    }

    public function logoutOtherDevices(): void
    {
        $this->validate([
            'logout_password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! Hash::check($this->logout_password, $user->password)) {
            $this->addError('logout_password', 'The password does not match our records.');

            return;
        }

        Auth::logoutOtherDevices($this->logout_password);

        $this->reset('logout_password');

        $this->dispatch('toast-show', message: 'Other devices logged out successfully.', type: 'success');
    }

    public function render()
    {
        $user = Auth::user()->load([
            'memberProfile',
            'socialLinks',
            'privacy',
            'membership',
            'notificationPreferences',
            'earnedBadges',
            'gamificationStats',
        ]);

        $badges = \App\Models\Badge::with(['users' => fn ($q) => $q->where('user_id', $user->id)])
            ->get()
            ->map(fn ($badge) => [
                'badge' => $badge,
                'earned' => $badge->users->isNotEmpty(),
                'earned_at' => $badge->users->first()?->pivot->earned_at,
            ]);

        $totalBadges = $badges->count();
        $earnedBadgeCount = $badges->where('earned', true)->count();

        $profileFields = [
            'name' => (bool) $this->name,
            'email' => (bool) $this->email,
            'registration_number' => (bool) $this->registration_number,
            'phone' => (bool) $this->phone,
            'bio' => (bool) $this->bio,
            'headline' => (bool) $this->headline,
            'program' => (bool) $this->program,
            'faculty' => (bool) $this->faculty,
            'year_of_study' => (bool) $this->year_of_study,
            'github_username' => (bool) $this->github_username,
            'linkedin_url' => (bool) $this->linkedin_url,
            'discord_username' => (bool) $this->discord_username,
            'profile_photo' => (bool) $user->profile_photo,
        ];

        $filledFields = collect($profileFields)->filter()->count();
        $totalFields = count($profileFields);
        $completionPercent = $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;

        return view('livewire.member-profile', [
            'user' => $user,
            'membership' => $user->membership,
            'badges' => $badges,
            'totalBadges' => $totalBadges,
            'earnedBadgeCount' => $earnedBadgeCount,
            'gamification' => $user->gamificationStats,
            'completionPercent' => $completionPercent,
            'filledFields' => $filledFields,
            'totalFields' => $totalFields,
            'profileFields' => $profileFields,
            'faculties' => config('academics.faculties'),
            'programsForFaculty' => collect(config('academics.faculties'))
                ->firstWhere('name', $this->faculty)['programs'] ?? [],
        ]);
    }
}
