<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MembershipCardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user()->load(['membership', 'memberProfile', 'earnedBadges']);

        $isPending = $user->membership_status === 'pending'
            || $user->membership?->status === 'pending';

        if ($isPending) {
            $joinedAt = $user->joined_at ?? $user->membership?->joined_at ?? $user->created_at;

            return view('frontend.membership-pending', [
                'user' => $user,
                'memberSince' => $joinedAt?->format('F Y') ?? 'N/A',
            ]);
        }

        $joinedAt = $user->joined_at ?? $user->membership?->joined_at ?? $user->created_at;
        $memberSince = $joinedAt?->format('F Y') ?? 'N/A';
        $memberId = '#'.str_pad((string) ($user->member_number ?? $user->id), 5, '0', STR_PAD_LEFT);
        $fullProgram = $user->program ?? $user->memberProfile?->program;
        $program = $this->programAbbreviation($fullProgram) ?? $fullProgram ?? 'N/A';

        $expiryDate = $user->membershipExpiryDate();
        $expiryFormatted = $expiryDate?->format('F j, Y') ?? 'N/A';

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
            'program' => $program,
            'fullProgram' => $fullProgram,
            'expiryFormatted' => $expiryFormatted,
            'issueDate' => $issueDate,
            'memberTypeLabel' => $memberTypeLabel,
            'qrCode' => $qrCode,
        ]);
    }

    private function programAbbreviation(?string $program): ?string
    {
        if (blank($program) || ! preg_match('/\(([^)]+)\)\s*$/', $program, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }
}
