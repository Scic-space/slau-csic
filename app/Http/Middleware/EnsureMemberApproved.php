<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPendingApproval()) {
            session()->flash('status', 'Your membership is pending approval by the club administration. Club activities will be unlocked once you are approved.');

            if ($request->inertia()) {
                return Inertia::location(route('dashboard'));
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
