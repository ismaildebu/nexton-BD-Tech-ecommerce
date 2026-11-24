<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorUser;
use Tests\Feature\Identity\IdentityTestCase;

class VendorStaffTest extends IdentityTestCase
{
    private function vendor(User $owner): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VND-STAFF-'.uniqid(),
            'business_name' => 'Staff Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function inviteStaff(User $owner, Vendor $vendor, User $staff, string $role = 'staff'): string
    {
        $response = $this->actingAs($owner)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email,
            'staff_role' => $role,
        ]);

        $response->assertRedirect(route('vendor.staff.index', $vendor));
        $token = $response->getSession()->get('invitation_token');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        return $token;
    }

    private function assertInvitationReissued(
        User $owner,
        User $staff,
        Vendor $vendor,
        VendorUser $membership,
        string $oldToken,
        ?string $oldTokenHash,
    ): void {
        $this->freezeTime();
        $newToken = $this->inviteStaff($owner, $vendor, $staff, 'manager');

        $this->assertNotSame($oldToken, $newToken);
        $this->assertDatabaseCount('vendor_users', 1);
        $this->assertDatabaseHas('vendor_users', ['id' => $membership->id, 'vendor_id' => $vendor->id]);

        $membership->refresh();
        $this->assertSame('invited', $membership->status);
        $this->assertSame('manager', $membership->staff_role);
        $this->assertSame($owner->id, $membership->invited_by);
        $this->assertSame(strtolower($staff->email), $membership->invited_email);
        $this->assertSame(hash('sha256', $newToken), $membership->invitation_token_hash);
        $this->assertNotSame($oldTokenHash, $membership->invitation_token_hash);
        $this->assertSame(now()->addDays(7)->toDateTimeString(), $membership->invitation_expires_at->toDateTimeString());
        $this->assertNull($membership->joined_at);
        $this->assertNull($membership->accepted_at);
        $this->assertNull($membership->declined_at);
        $this->assertNull($membership->revoked_at);

        $this->actingAs($staff)->get(route('vendor.staff.invitation', $oldToken))->assertNotFound();
        $this->actingAs($staff)->get(route('vendor.staff.invitation', $newToken))
            ->assertOk()
            ->assertSee('Accept invitation');
    }

    public function test_owner_can_invite_staff_without_activating_them(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);

        $this->actingAs($owner)
            ->post(route('vendor.staff.store', $vendor), [
                'email' => $staff->email,
                'staff_role' => 'manager',
            ])
            ->assertRedirect(route('vendor.staff.index', $vendor));

        $this->assertDatabaseHas('vendor_users', [
            'vendor_id' => $vendor->id,
            'user_id' => $staff->id,
            'staff_role' => 'manager',
            'status' => 'invited',
            'joined_at' => null,
        ]);
    }

    public function test_active_staff_dashboard_resolves_the_membership_vendor(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        VendorUser::create([
            'vendor_id' => $vendor->id,
            'user_id' => $staff->id,
            'staff_role' => 'manager',
            'status' => 'active',
            'joined_at' => now(),
            'accepted_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee($vendor->business_name)
            ->assertDontSee('No vendor profile yet.');

        $response->assertViewHas('vendor', fn (Vendor $resolved): bool => $resolved->is($vendor));
    }

    public function test_non_owner_cannot_invite_staff(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);

        $this->actingAs($intruder)
            ->post(route('vendor.staff.store', $vendor), [
                'email' => $staff->email,
                'staff_role' => 'staff',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('vendor_users', [
            'vendor_id' => $vendor->id,
            'user_id' => $staff->id,
        ]);
    }

    public function test_privileged_staff_roles_cannot_be_assigned(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);

        $this->actingAs($owner)
            ->post(route('vendor.staff.store', $vendor), [
                'email' => $staff->email,
                'staff_role' => 'owner',
            ])
            ->assertSessionHasErrors('staff_role');

        $this->assertDatabaseMissing('vendor_users', [
            'vendor_id' => $vendor->id,
            'user_id' => $staff->id,
        ]);
    }

    public function test_invitee_can_accept_a_hashed_one_time_invitation(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);

        $response = $this->actingAs($owner)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email, 'staff_role' => 'manager',
        ]);
        $token = $response->getSession()->get('invitation_token');
        $membership = VendorUser::firstOrFail();

        $this->assertNotEmpty($token);
        $this->assertSame(hash('sha256', $token), $membership->invitation_token_hash);
        $this->assertNotSame($token, $membership->invitation_token_hash);

        $this->actingAs($staff)->get(route('vendor.staff.invitation', $token))->assertOk()->assertSee('Accept invitation');
        $this->actingAs($staff)->post(route('vendor.staff.invitation.accept', $token))
            ->assertRedirect(route('vendor.products.index'));

        $membership->refresh();
        $this->assertSame('active', $membership->status);
        $this->assertNotNull($membership->joined_at);
        $this->assertNull($membership->invitation_token_hash);
        $this->actingAs($staff)->get(route('vendor.staff.invitation', $token))->assertNotFound();
    }

    public function test_invitee_can_decline_an_invitation(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $response = $this->actingAs($owner)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email, 'staff_role' => 'staff',
        ]);
        $token = $response->getSession()->get('invitation_token');

        $this->actingAs($staff)->post(route('vendor.staff.invitation.decline', $token))->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('vendor_users', ['vendor_id' => $vendor->id, 'status' => 'declined', 'invitation_token_hash' => null]);
    }

    public function test_owner_can_revoke_a_pending_invitation(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $response = $this->actingAs($owner)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email, 'staff_role' => 'staff',
        ]);
        $token = $response->getSession()->get('invitation_token');
        $membership = VendorUser::firstOrFail();

        $this->actingAs($owner)->post(route('vendor.staff.revoke', [$vendor, $membership]))
            ->assertRedirect(route('vendor.staff.index', $vendor));
        $this->assertDatabaseHas('vendor_users', ['id' => $membership->id, 'status' => 'revoked', 'invitation_token_hash' => null]);
        $this->actingAs($staff)->get(route('vendor.staff.invitation', $token))->assertNotFound();
    }

    public function test_owner_can_reissue_a_declined_invitation_on_the_same_membership(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $oldToken = $this->inviteStaff($owner, $vendor, $staff);
        $membership = VendorUser::firstOrFail();
        $oldTokenHash = $membership->invitation_token_hash;

        $this->actingAs($staff)->post(route('vendor.staff.invitation.decline', $oldToken))
            ->assertRedirect(route('profile.edit'));
        $membership->refresh();
        $this->assertSame('declined', $membership->status);
        $this->assertNotNull($membership->declined_at);

        $this->assertInvitationReissued($owner, $staff, $vendor, $membership, $oldToken, $oldTokenHash);
    }

    public function test_owner_can_reissue_a_revoked_invitation_on_the_same_membership(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $oldToken = $this->inviteStaff($owner, $vendor, $staff);
        $membership = VendorUser::firstOrFail();
        $oldTokenHash = $membership->invitation_token_hash;

        $this->actingAs($owner)->post(route('vendor.staff.revoke', [$vendor, $membership]))
            ->assertRedirect(route('vendor.staff.index', $vendor));
        $membership->refresh();
        $this->assertSame('revoked', $membership->status);
        $this->assertNotNull($membership->revoked_at);

        $this->assertInvitationReissued($owner, $staff, $vendor, $membership, $oldToken, $oldTokenHash);
    }

    public function test_owner_can_reissue_an_expired_invitation_and_invalidate_its_old_token(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $this->freezeTime();
        $oldToken = $this->inviteStaff($owner, $vendor, $staff);
        $membership = VendorUser::firstOrFail();
        $oldTokenHash = $membership->invitation_token_hash;
        $membership->update(['invitation_expires_at' => now()->subDay()]);

        $this->assertInvitationReissued($owner, $staff, $vendor, $membership, $oldToken, $oldTokenHash);
    }

    public function test_pending_unexpired_invitation_cannot_be_reissued_as_a_duplicate(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $this->inviteStaff($owner, $vendor, $staff);
        $membership = VendorUser::firstOrFail();
        $tokenHash = $membership->invitation_token_hash;
        $expiresAt = $membership->invitation_expires_at->toDateTimeString();

        $this->actingAs($owner)
            ->from(route('vendor.staff.index', $vendor))
            ->post(route('vendor.staff.store', $vendor), [
                'email' => $staff->email,
                'staff_role' => 'manager',
            ])
            ->assertRedirect(route('vendor.staff.index', $vendor))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('vendor_users', 1);
        $membership->refresh();
        $this->assertSame('invited', $membership->status);
        $this->assertSame('staff', $membership->staff_role);
        $this->assertSame($tokenHash, $membership->invitation_token_hash);
        $this->assertSame($expiresAt, $membership->invitation_expires_at->toDateTimeString());
    }

    public function test_active_staff_cannot_be_reinvited_to_the_same_vendor(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $token = $this->inviteStaff($owner, $vendor, $staff, 'manager');
        $membership = VendorUser::firstOrFail();

        $this->actingAs($staff)->post(route('vendor.staff.invitation.accept', $token))
            ->assertRedirect(route('vendor.products.index'));

        $this->actingAs($owner)
            ->from(route('vendor.staff.index', $vendor))
            ->post(route('vendor.staff.store', $vendor), [
                'email' => $staff->email,
                'staff_role' => 'staff',
            ])
            ->assertRedirect(route('vendor.staff.index', $vendor))
            ->assertSessionHasErrors('email');
        $this->get(route('vendor.staff.index', $vendor))
            ->assertSee('This person already belongs to the vendor team.');

        $this->assertDatabaseCount('vendor_users', 1);
        $membership->refresh();
        $this->assertSame('active', $membership->status);
        $this->assertSame('manager', $membership->staff_role);
        $this->assertNotNull($membership->joined_at);
        $this->assertNotNull($membership->accepted_at);
        $this->assertNull($membership->invitation_token_hash);
    }

    public function test_unknown_and_duplicate_email_validation_is_visible(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        $index = route('vendor.staff.index', $vendor);

        $this->actingAs($owner)->from($index)->post(route('vendor.staff.store', $vendor), [
            'email' => 'missing@example.com', 'staff_role' => 'staff',
        ])->assertSessionHasErrors('email');
        $this->get($index)->assertSee('No account was found for this email address.');

        $this->actingAs($owner)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email, 'staff_role' => 'staff',
        ])->assertRedirect();
        $this->actingAs($owner)->from($index)->post(route('vendor.staff.store', $vendor), [
            'email' => $staff->email, 'staff_role' => 'manager',
        ])->assertSessionHasErrors('email');
        $this->get($index)->assertSee('This person already belongs to the vendor team.');
    }

    public function test_active_staff_cannot_manage_another_vendors_team(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();
        $staffA = User::factory()->create();
        $inviteeB = User::factory()->create();
        $vendorA = $this->vendor($ownerA);
        $vendorB = $this->vendor($ownerB);
        VendorUser::create(['vendor_id' => $vendorA->id, 'user_id' => $staffA->id, 'staff_role' => 'manager', 'status' => 'active']);
        $response = $this->actingAs($ownerB)->post(route('vendor.staff.store', $vendorB), [
            'email' => $inviteeB->email, 'staff_role' => 'staff',
        ]);
        $token = $response->getSession()->get('invitation_token');
        $membershipB = VendorUser::where('vendor_id', $vendorB->id)->firstOrFail();

        $this->actingAs($staffA)->get(route('vendor.staff.index', $vendorB))->assertForbidden();
        $this->actingAs($staffA)->post(route('vendor.staff.store', $vendorB), [
            'email' => $inviteeB->email, 'staff_role' => 'staff',
        ])->assertForbidden();
        $this->actingAs($staffA)->post(route('vendor.staff.revoke', [$vendorB, $membershipB]))->assertForbidden();
        $this->actingAs($staffA)->post(route('vendor.staff.invitation.accept', $token))->assertSessionHasErrors('email');
    }
}
