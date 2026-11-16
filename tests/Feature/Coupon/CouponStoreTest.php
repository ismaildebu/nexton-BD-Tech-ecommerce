<?php

namespace Tests\Feature\Coupon;

use App\Models\User;
use Tests\Feature\Identity\IdentityTestCase;

class CouponStoreTest extends IdentityTestCase
{
    public function test_admin_can_create_coupon(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), ['code' => 'SAVE10', 'type' => 'percent', 'value' => 10])
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', ['code' => 'SAVE10']);
    }
}
