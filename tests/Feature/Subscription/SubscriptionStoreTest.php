<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class SubscriptionStoreTest extends IdentityTestCase
{
    public function test_user_can_create_subscription(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-SUB001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.store'), ['vendor_id' => $vendor->id, 'plan' => 'basic'])
            ->assertRedirect(route('subscriptions.index'));

        $this->assertDatabaseHas('subscriptions', ['vendor_id' => $vendor->id]);
    }
}
