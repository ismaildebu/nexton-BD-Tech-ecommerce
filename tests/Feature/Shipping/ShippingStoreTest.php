<?php

namespace Tests\Feature\Shipping;

use App\Models\User;
use App\Modules\Order\Models\Order;
use Tests\Feature\Identity\IdentityTestCase;

class ShippingStoreTest extends IdentityTestCase
{
    private function order(User $user, string $number): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_number' => $number,
            'total' => 100,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_customer_cannot_create_a_shipment_for_own_order(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, 'ORD-S001');

        $this->actingAs($user)
            ->post(route('shipping.store'), ['order_id' => $order->id, 'carrier' => 'Pathao'])
            ->assertForbidden();

        $this->assertDatabaseMissing('shippings', ['order_id' => $order->id]);
    }

    public function test_user_cannot_create_shipment_for_another_users_order(): void
    {
        $intruder = User::factory()->create();
        $order = $this->order(User::factory()->create(), 'ORD-S002');

        $this->actingAs($intruder)
            ->post(route('shipping.store'), ['order_id' => $order->id, 'carrier' => 'Pathao'])
            ->assertForbidden();

        $this->assertDatabaseMissing('shippings', ['order_id' => $order->id]);
    }

    public function test_admin_shipping_form_displays_the_eligible_order_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->order(User::factory()->create(), 'ORD-S003');

        $this->actingAs($admin)
            ->get(route('admin.shipping.index'))
            ->assertOk()
            ->assertSee($order->order_number.' (pending)', false);
    }

    public function test_admin_can_create_a_shipment_from_the_guarded_endpoint(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->order(User::factory()->create(), 'ORD-S003');

        $this->actingAs($admin)
            ->post(route('shipping.store'), [
                'order_id' => $order->id,
                'carrier' => 'Pathao',
                'tracking_number' => 'TRACK-ADMIN',
            ])
            ->assertRedirect(route('shipping.index'));

        $this->assertDatabaseHas('shippings', [
            'order_id' => $order->id,
            'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-ADMIN',
            'status' => 'pending',
        ]);
    }
}
