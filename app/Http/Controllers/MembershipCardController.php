<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MembershipCardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user()->load(['membership', 'earnedBadges']);

        $joinedAt = $user->joined_at ?? $user->membership?->joined_at ?? $user->created_at;
        $memberSince = $joinedAt?->format('F Y') ?? 'N/A';
        $memberId = $user->student_id ?? '#'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);

        $expiryDate = $user->membership_expires_at ?? $user->membership?->expires_at ?? null;
        $expiryFormatted = $expiryDate ? \Carbon\Carbon::parse($expiryDate)->format('F j, Y') : 'N/A';

        $issueDate = now()->format('F j, Y');

        $memberTypeLabel = match ($user->membership_type) {
            'alumni' => 'Alumni Member',
            'associate' => 'Associate Member',
            default => 'Active Member',
        };

        $qrCode = null;
        try {
            $qrCode = QrCode::size(120)
                ->backgroundColor(255, 255, 255)
                ->color(0, 0, 0)
                ->generate(route('members.public.show', $user));
        } catch (\Throwable) {
            // QR generation failed silently
        }

        return view('frontend.membership-card', [
            'member' => $user,
            'memberSince' => $memberSince,
            'memberId' => $memberId,
            'expiryFormatted' => $expiryFormatted,
            'issueDate' => $issueDate,
            'memberTypeLabel' => $memberTypeLabel,
            'qrCode' => $qrCode,
        ]);
    }
}
