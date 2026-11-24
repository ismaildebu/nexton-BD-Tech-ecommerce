<?php

namespace App\Modules\Vendor\Services;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorUser;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffInvitationService
{
    private const ROLES = ['manager', 'staff'];

    public function invite(Vendor $vendor, User $owner, string $email, string $role): array
    {
        if (! in_array($role, self::ROLES, true)) {
            throw ValidationException::withMessages(['staff_role' => __('The selected staff role is invalid.')]);
        }

        $email = Str::lower(trim($email));

        return DB::transaction(function () use ($vendor, $owner, $email, $role) {
            $lockedVendor = Vendor::whereKey($vendor->getKey())->lockForUpdate()->firstOrFail();
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user === null) {
                throw ValidationException::withMessages(['email' => __('No account was found for this email address.')]);
            }

            $membership = $lockedVendor->staff()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if ($membership !== null) {
                $canReissue = in_array($membership->status, ['declined', 'revoked'], true)
                    || ($membership->status === 'invited' && ! $membership->isPendingInvitation());

                if (! $canReissue) {
                    throw ValidationException::withMessages(['email' => __('This person already belongs to the vendor team.')]);
                }
            }

            $token = Str::random(64);

            if ($membership === null) {
                $membership = $lockedVendor->staff()->create([
                    'user_id' => $user->getKey(),
                    'invited_email' => $email,
                    'staff_role' => $role,
                    'status' => 'invited',
                    'invited_by' => $owner->getKey(),
                    'invitation_token_hash' => $this->hashToken($token),
                    'invitation_expires_at' => now()->addDays(7),
                ]);
            } else {
                $membership->update([
                    'invited_email' => $email,
                    'staff_role' => $role,
                    'status' => 'invited',
                    'invited_by' => $owner->getKey(),
                    'joined_at' => null,
                    'accepted_at' => null,
                    'declined_at' => null,
                    'revoked_at' => null,
                    'invitation_token_hash' => null,
                    'invitation_expires_at' => null,
                ]);

                $membership->update([
                    'invitation_token_hash' => $this->hashToken($token),
                    'invitation_expires_at' => now()->addDays(7),
                ]);
            }

            return ['membership' => $membership, 'token' => $token];
        });
    }

    public function findByToken(string $token): VendorUser
    {
        $membership = VendorUser::where('invitation_token_hash', $this->hashToken($token))
            ->with('vendor')
            ->first();

        if ($membership === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        if (! $membership->isPendingInvitation()) {
            throw new DomainException(__('This invitation is expired, revoked, or no longer pending.'));
        }

        return $membership;
    }

    public function accept(string $token, User $invitee): VendorUser
    {
        return DB::transaction(function () use ($token, $invitee) {
            $membership = VendorUser::where('invitation_token_hash', $this->hashToken($token))
                ->lockForUpdate()
                ->first();

            if ($membership === null) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            $this->authorizeInvitee($membership, $invitee);

            $membership->update([
                'status' => 'active',
                'joined_at' => now(),
                'accepted_at' => now(),
                'invitation_token_hash' => null,
                'invitation_expires_at' => null,
            ]);

            return $membership->refresh();
        });
    }

    public function decline(string $token, User $invitee): VendorUser
    {
        return DB::transaction(function () use ($token, $invitee) {
            $membership = VendorUser::where('invitation_token_hash', $this->hashToken($token))
                ->lockForUpdate()
                ->first();

            if ($membership === null) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            $this->authorizeInvitee($membership, $invitee);

            $membership->update([
                'status' => 'declined',
                'declined_at' => now(),
                'invitation_token_hash' => null,
                'invitation_expires_at' => null,
            ]);

            return $membership->refresh();
        });
    }

    public function revoke(Vendor $vendor, VendorUser $membership): void
    {
        DB::transaction(function () use ($vendor, $membership) {
            $locked = VendorUser::whereKey($membership->getKey())
                ->where('vendor_id', $vendor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'invited') {
                throw new DomainException(__('Only pending invitations can be revoked.'));
            }

            $locked->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'invitation_token_hash' => null,
                'invitation_expires_at' => null,
            ]);
        });
    }

    private function authorizeInvitee(VendorUser $membership, User $invitee): void
    {
        if (! $membership->isPendingInvitation()) {
            throw new DomainException(__('This invitation is expired, revoked, or no longer pending.'));
        }

        if ($membership->user_id !== $invitee->getKey()
            || Str::lower((string) $membership->invited_email) !== Str::lower((string) $invitee->email)) {
            throw ValidationException::withMessages(['email' => __('This invitation belongs to a different account.')]);
        }
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
