<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRegistrationRequest;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(protected MembershipService $membershipService) {}

    public function create(): View
    {
        return view('pages.auth.register');
    }

    public function store(StoreMemberRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['joined_at'] = now()->toDateString();
        $validated['membership_type'] = 'active';
        $validated['membership_status'] = 'pending';
        $validated['privacy_settings'] = [
            'show_email' => false,
            'show_phone' => false,
            'show_discord' => false,
            'show_attendance' => false,
            'show_program' => true,
            'show_year' => true,
            'allow_contact' => true,
            'show_profile' => true,
        ];

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        unset($validated['terms']);

        event(new Registered(($user = User::create($validated))));

        $this->membershipService->registerPending($user);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
