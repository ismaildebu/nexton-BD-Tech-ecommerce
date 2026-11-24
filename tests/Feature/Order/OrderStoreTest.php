<?php

namespace Tests\Feature\Order;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\Feature\Identity\IdentityTestCase;

class OrderStoreTest extends IdentityTestCase
{
    public function test_user_cannot_create_zero_total_order_without_cart(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('orders.store'), [
                'address_id' => $address->id,
                'idempotency_key' => (string) Str::uuid(),
                'total' => 100,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }
}
