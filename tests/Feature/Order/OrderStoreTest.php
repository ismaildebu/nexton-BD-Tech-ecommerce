<?php

namespace Tests\Feature\Order;

use App\Models\User;
use Tests\Feature\Identity\IdentityTestCase;

class OrderStoreTest extends IdentityTestCase
{
    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('orders.store'), ['total' => 100])
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
    }
}
