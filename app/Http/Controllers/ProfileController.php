<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user()->load([
            'memberProfile',
            'socialLinks',
            'privacy',
        ]);

        return Inertia::render('members/Profile', [
            'adminDashboardUrl' => route('filament.admin.pages.dashboard'),
            'memberDashboardUrl' => route('dashboard'),
            'profile' => $user->memberProfile,
            'social' => $user->socialLinks,
            'privacy' => $user->privacy,
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
                'registration_number' => $user->registration_number,
                'membership_type' => $user->membership_type,
                'membership_status' => $user->membership_status,
                'discord_username' => $user->discord_username,
                'is_discord_member' => $user->is_discord_member,
                'avatar_url' => $user->avatar_url,
            ],
            'activities' => $user->actions()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn ($a) => [
                    'description' => $a->description,
                    'date' => $a->created_at->diffForHumans(),
                ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'registration_number' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($user->id), 'regex:/^[A-Za-z]+\/\d{2}[DW]\/[A-Za-z]\/[A-Za-z]\d+$/'],
            'phone' => ['nullable', 'string', 'max:30'],
            'program' => ['nullable', 'string', 'max:255'],
            'faculty' => ['nullable', 'string', 'max:255'],
            'year_of_study' => ['nullable', 'integer', 'min:1', 'max:6'],
            'intake' => ['nullable', 'string', 'in:january,may,august'],
            'intake_year' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            'bio' => ['nullable', 'string', 'max:1000'],
            'headline' => ['nullable', 'string', 'max:255'],
            'github_username' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'discord_username' => ['nullable', 'string', 'max:255'],
            'is_discord_member' => ['boolean'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'registration_number' => $validated['registration_number'] ?? null,
            'discord_username' => $validated['discord_username'] ?? null,
            'is_discord_member' => $validated['is_discord_member'] ?? false,
            'intake' => $validated['intake'] ?? null,
            'intake_year' => $validated['intake_year'] ?? null,
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if ($validated['password'] ?? false) {
            $user->password = Hash::make($validated['password']);
        }

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

        return redirect()->back()->with('flash', ['success' => 'Profile updated successfully.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password does not match our records.'],
            ]);
        }

        $user = Auth::user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
