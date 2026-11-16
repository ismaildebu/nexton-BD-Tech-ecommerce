<?php

namespace Tests\Feature\Accounting;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class SettlementStoreTest extends IdentityTestCase
{
    public function test_user_can_create_settlement(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-SET001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('settlements.store'), ['vendor_id' => $vendor->id, 'amount' => 50])
            ->assertRedirect(route('settlements.index'));

        $this->assertDatabaseHas('settlements', ['vendor_id' => $vendor->id]);
    }
}
