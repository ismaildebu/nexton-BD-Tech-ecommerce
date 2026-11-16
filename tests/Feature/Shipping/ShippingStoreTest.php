<?php

namespace Tests\Feature\Shipping;

use App\Models\User;
use App\Modules\Order\Models\Order;
use Tests\Feature\Identity\IdentityTestCase;

class ShippingStoreTest extends IdentityTestCase
{
    public function test_user_can_create_shipment(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-S001',
            'total' => 100,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->post(route('shipping.store'), ['order_id' => $order->id, 'carrier' => 'Pathao'])
            ->assertRedirect(route('shipping.index'));

        $this->assertDatabaseHas('shippings', ['order_id' => $order->id]);
    }
}
