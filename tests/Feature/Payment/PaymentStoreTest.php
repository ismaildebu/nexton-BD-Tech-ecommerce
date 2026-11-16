<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Modules\Order\Models\Order;
use Tests\Feature\Identity\IdentityTestCase;

class PaymentStoreTest extends IdentityTestCase
{
    public function test_user_can_record_payment(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-T001',
            'total' => 100,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), ['order_id' => $order->id, 'amount' => 100])
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', ['order_id' => $order->id]);
    }
}
