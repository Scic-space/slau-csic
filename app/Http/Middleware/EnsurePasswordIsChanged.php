<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Redirect members with a forced password change to their profile
     * until they set a new password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->force_password_change
            && ! $request->is('livewire/*')
            && ! $request->routeIs(
                'profile.edit',
                'profile.update',
                'logout',
                'auth.logout',
                'password.request',
                'password.email',
                'password.reset',
                'password.store',
                'verification.*'
            )) {
            session()->flash('status', 'An administrator reset your password. Please set a new password to continue.');

            if ($request->inertia()) {
                return Inertia::location(route('profile.edit'));
            }

            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}
