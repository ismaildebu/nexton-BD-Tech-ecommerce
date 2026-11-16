<?php

namespace Tests\Feature\Commission;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class CommissionStoreTest extends IdentityTestCase
{
    public function test_user_can_record_commission(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-COM001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('commissions.store'), ['vendor_id' => $vendor->id, 'amount' => 10, 'rate' => 5])
            ->assertRedirect(route('commissions.index'));

        $this->assertDatabaseHas('commissions', ['vendor_id' => $vendor->id]);
    }
}
