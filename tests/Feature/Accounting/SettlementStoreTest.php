<?php

namespace Tests\Feature\Accounting;

use App\Models\User;
use App\Modules\Accounting\Models\Settlement;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class SettlementStoreTest extends IdentityTestCase
{
    private function vendor(User $owner, string $code): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => $code,
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function settlement(Vendor $vendor, string $status = 'pending'): Settlement
    {
        return Settlement::create([
            'vendor_id' => $vendor->id,
            'amount' => 50,
            'status' => $status,
        ]);
    }

    public function test_vendor_owner_can_create_settlement(): void
    {
        $user = User::factory()->create();
        $vendor = $this->vendor($user, 'VND-SET001');

        $this->actingAs($user)
            ->post(route('settlements.store'), ['vendor_id' => $vendor->id, 'amount' => 50])
            ->assertRedirect(route('settlements.index'));

        $this->assertDatabaseHas('settlements', [
            'vendor_id' => $vendor->id,
            'amount' => 50,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_create_settlement_for_another_vendor(): void
    {
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET002');

        $this->actingAs(User::factory()->create())
            ->post(route('settlements.store'), ['vendor_id' => $vendor->id, 'amount' => 50])
            ->assertForbidden();

        $this->assertDatabaseCount('settlements', 0);
    }

    public function test_admin_can_create_a_pending_settlement(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET003');

        $this->actingAs($admin)
            ->post(route('admin.settlements.store'), ['vendor_id' => $vendor->id, 'amount' => 125.50])
            ->assertRedirect(route('admin.settlements.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('settlements', [
            'vendor_id' => $vendor->id,
            'amount' => 125.50,
            'status' => 'pending',
        ]);
    }

    public function test_admin_settlement_creation_shows_vendor_and_amount_validation_errors(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET013');

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.store'), ['vendor_id' => 999999, 'amount' => 50])
            ->assertSessionHasErrors('vendor_id');

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.store'), ['vendor_id' => $vendor->id, 'amount' => 10000000000])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('settlements', 0);
    }

    public function test_admin_can_move_a_settlement_from_pending_through_processing_to_paid(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET004');
        $settlement = $this->settlement($vendor);

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.transition', $settlement), ['status' => 'processing'])
            ->assertRedirect(route('admin.settlements.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => 'processing']);

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.transition', $settlement), ['status' => 'paid'])
            ->assertRedirect(route('admin.settlements.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => 'paid']);
    }

    public function test_admin_can_move_a_processing_settlement_to_failed(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET005');
        $settlement = $this->settlement($vendor, 'processing');

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.transition', $settlement), ['status' => 'failed'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => 'failed']);
    }

    public function test_invalid_settlement_transitions_are_rejected_without_mutation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET006');

        foreach ([
            ['pending', 'paid'],
            ['pending', 'failed'],
            ['processing', 'processing'],
            ['paid', 'processing'],
            ['paid', 'failed'],
            ['failed', 'processing'],
            ['failed', 'paid'],
        ] as [$current, $target]) {
            $settlement = $this->settlement($vendor, $current);

            $this->actingAs($admin)
                ->from(route('admin.settlements.index'))
                ->post(route('admin.settlements.transition', $settlement), ['status' => $target])
                ->assertRedirect(route('admin.settlements.index'))
                ->assertSessionHas('error');

            $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => $current]);
        }
    }

    public function test_non_admin_cannot_transition_a_settlement(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->vendor($owner, 'VND-SET007');
        $settlement = $this->settlement($vendor);

        $this->actingAs($owner)
            ->post(route('admin.settlements.transition', $settlement), ['status' => 'processing'])
            ->assertForbidden();

        $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => 'pending']);
    }

    public function test_settlement_transition_rejects_an_unsupported_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET008');
        $settlement = $this->settlement($vendor);

        $this->actingAs($admin)
            ->from(route('admin.settlements.index'))
            ->post(route('admin.settlements.transition', $settlement), ['status' => 'pending'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('settlements', ['id' => $settlement->id, 'status' => 'pending']);
    }

    public function test_admin_settlement_page_exposes_only_valid_workflow_actions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SET009');
        $this->settlement($vendor, 'pending');
        $this->settlement($vendor, 'processing');
        $this->settlement($vendor, 'paid');

        $this->actingAs($admin)
            ->get(route('admin.settlements.index'))
            ->assertOk()
            ->assertSee('Create settlement')
            ->assertSee('Start processing')
            ->assertSee('Mark paid')
            ->assertSee('Mark failed')
            ->assertSee('Final status');
    }
}
