<?php

namespace App\Http\Middleware;

use App\Models\StaffNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    'membership_status' => $request->user()->membership?->status,
                    'membership_type' => $request->user()->membership?->type,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                'receipt_code' => fn () => $request->session()->get('receipt_code'),
            ],
            'recentNotifications' => $request->user()
                ? StaffNotification::where('staff_id', $request->user()->id)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($n) => [
                        'id' => $n->id,
                        'type' => $n->type ?? 'info',
                        'title' => $n->title,
                        'message' => $n->message ?? '',
                        'priority' => $n->priority ?? 'normal',
                        'action_required' => $n->action_required ?? false,
                        'action_url' => $n->action_url,
                        'is_read' => $n->is_read,
                        'created_at' => $n->created_at,
                    ])
                : [],
        ];
    }
}
