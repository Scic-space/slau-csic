<?php

namespace App\Services;

use App\Events\MemberApproved;
use App\Events\MemberRegistered;
use App\Events\MemberRejected;
use App\Events\MemberSuspended;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function registerPending(User $user, array $data): Membership
    {
        return DB::transaction(function () use ($user) {
            $membership = Membership::create([
                'user_id' => $user->id,
                'type' => 'active',
                'status' => 'pending',
                'joined_at' => now(),
            ]);

            $user->update([
                'membership_type' => 'active',
                'membership_status' => 'pending',
                'approved_at' => null,
            ]);

            MemberRegistered::dispatch($user, $membership);

            return $membership;
        });
    }

    public function approve(Membership $membership, User $approver, ?string $notes = null): Membership
    {
        return DB::transaction(function () use ($membership, $approver, $notes) {
            $membership->update([
                'status' => 'active',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $user = $membership->user;
            $user->update([
                'membership_status' => 'active',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'membership_expires_at' => $user->membershipExpiryDate(),
            ]);
            $user->assignMemberNumber();
            $user->assignRole('member');

            MemberApproved::dispatch($user, $membership, $approver);

            return $membership;
        });
    }

    public function reject(Membership $membership, User $rejecter, ?string $notes = null): Membership
    {
        return DB::transaction(function () use ($membership, $rejecter, $notes) {
            $membership->update([
                'status' => 'rejected',
                'approved_by' => $rejecter->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $user = $membership->user;
            $user->update([
                'membership_status' => 'rejected',
                'approved_by' => $rejecter->id,
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            MemberRejected::dispatch($user, $membership, $rejecter);

            return $membership;
        });
    }

    public function suspend(Membership $membership, User $suspender, string $reason, ?string $until = null): Membership
    {
        return DB::transaction(function () use ($membership, $suspender, $reason, $until) {
            $membership->update([
                'status' => 'suspended',
                'suspension_reason' => $reason,
                'suspended_by' => $suspender->id,
                'suspended_until' => $until,
            ]);

            $user = $membership->user;
            $user->update([
                'membership_status' => 'suspended',
                'suspension_reason' => $reason,
                'suspended_by' => $suspender->id,
                'suspended_until' => $until,
            ]);

            MemberSuspended::dispatch($user, $membership, $suspender);

            return $membership;
        });
    }

    public function reactivate(Membership $membership): Membership
    {
        $membership->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_until' => null,
            'suspended_by' => null,
        ]);

        $membership->user->update([
            'membership_status' => 'active',
            'suspension_reason' => null,
            'suspended_until' => null,
            'suspended_by' => null,
        ]);

        return $membership;
    }

    public function convertToAlumni(Membership $membership): Membership
    {
        $membership->update([
            'type' => 'alumni',
            'status' => 'active',
            'left_at' => now(),
        ]);

        $user = $membership->user;
        $user->update([
            'membership_type' => 'alumni',
            'membership_status' => 'active',
        ]);
        $user->syncRoles(['alumni']);

        return $membership;
    }

    public function markAsLeft(Membership $membership): Membership
    {
        $membership->update([
            'status' => 'left',
            'left_at' => now(),
        ]);

        $membership->user->update([
            'membership_status' => 'left',
        ]);

        return $membership;
    }
}
