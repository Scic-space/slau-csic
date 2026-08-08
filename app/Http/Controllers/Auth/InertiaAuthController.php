<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipService;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InertiaAuthController extends Controller
{
    public function __construct(
        protected MembershipService $membershipService,
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('auth/Login');
    }

    public function login(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $key = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($key),
                    'minutes' => ceil(RateLimiter::availableIn($key) / 60),
                ]),
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user && $user->hasAnyRole(['super-admin', 'admin', 'treasurer', 'president'])) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return \Inertia\Inertia::location(route('dashboard'));
    }

    public function showRegister(): Response
    {
        return Inertia::render('auth/Register', [
            'faculties' => array_values(config('academics.faculties')),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:users,registration_number', 'regex:/^[A-Za-z]+\/\d{2}[DW]\/[A-Za-z]\/[A-Za-z]\d+$/'],
            'phone' => ['required', 'string', 'max:30'],
            'program' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                    $programs = collect(config('academics.faculties'))
                        ->firstWhere('name', $request->input('faculty'))['programs'] ?? [];

                    if (! in_array($value, $programs, true)) {
                        $fail('program.in');
                    }
                },
            ],
            'faculty' => ['required', 'string', 'max:255', Rule::in(config('academics.facultyNames'))],
            'year_of_study' => ['required', 'integer', 'min:1', 'max:6'],
            'intake' => ['required', 'string', 'in:january,may,august'],
            'intake_year' => ['required', 'integer', 'min:1990', 'max:'.now()->year],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'terms' => ['accepted'],
        ], [
            'registration_number.required' => 'Your university registration number is required.',
            'registration_number.regex' => 'Format must be like BACS/26D/U/A0000 (Course/Year+Mode/Country/Intake+Number).',
            'registration_number.unique' => 'This registration number is already registered.',
            'phone.required' => 'Phone number is required.',
            'program.required' => 'Programme is required.',
            'year_of_study.required' => 'Year of study is required.',
            'intake.required' => 'Please select your intake (January, May or August).',
            'intake.in' => 'Intake must be January, May or August.',
            'intake_year.required' => 'Please enter the year you were admitted (your intake year).',
            'intake_year.max' => 'Your intake year cannot be in the future.',
            'intake_year.min' => 'Your intake year must be a valid four-digit year.',
            'faculty.required' => 'Please select your faculty.',
            'faculty.in' => 'Please select a valid faculty from the list.',
            'program.required' => 'Please select your programme.',
            'program.in' => 'Please select a valid programme from the list.',
            'terms.accepted' => 'You need to accept the club platform terms before continuing.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'registration_number' => $request->registration_number,
            'joined_at' => now(),
            'membership_status' => 'pending',
            'membership_type' => 'active',
            'intake' => $request->intake,
            'intake_year' => $request->intake_year,
        ]);

        $user->memberProfile()->create([
            'phone' => $request->phone,
            'program' => $request->program,
            'faculty' => $request->faculty,
            'year_of_study' => $request->year_of_study,
        ]);

        $user->socialLinks()->create();
        $user->privacy()->create();

        $this->membershipService->registerPending($user, $request->all());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('auth/ForgotPassword');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect to the member dashboard, forcing a full page load for Inertia
     * requests because /dashboard is rendered by Livewire, not Inertia.
     */
    protected function redirectToDashboard(Request $request, array $params = []): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->inertia()) {
            return Inertia::location(route('dashboard', $params));
        }

        return redirect()->route('dashboard', $params);
    }

    public function showVerifyEmail(): Response
    {
        return Inertia::render('auth/VerifyEmail');
    }

    public function verifyEmailCode(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->redirectToDashboard($request);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        if (! $user->emailVerificationCodeIsValid($validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid or has expired. Request a new code and try again.',
            ]);
        }

        $user->markEmailAsVerified();
        $user->clearEmailVerificationCode();

        event(new Verified($user));

        return $this->redirectToDashboard($request, ['verified' => 1]);
    }

    public function resendVerification(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectToDashboard($request);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-code-sent');
    }
}
