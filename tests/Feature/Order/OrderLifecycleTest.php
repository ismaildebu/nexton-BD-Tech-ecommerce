<?php

namespace Tests\Feature\Order;

use App\Models\Address;
use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Product\Models\Product;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\Feature\Identity\IdentityTestCase;

class OrderLifecycleTest extends IdentityTestCase
{
    private function context(string $orderStatus = 'pending', string $paymentStatus = 'unpaid'): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-ORD-'.uniqid(),
            'business_name' => 'Order Lifecycle Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Lifecycle Product',
            'slug' => 'lifecycle-product-'.uniqid(),
            'price' => 50,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $inventory = Inventory::create(['product_id' => $product->id, 'quantity' => 3]);
        $address = Address::factory()->for($user)->create();
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_address' => Arr::only($address->getAttributes(), [
                'type', 'full_name', 'phone', 'address_line', 'division', 'district', 'upazila', 'postal_code',
            ]),
            'order_number' => 'ORD-'.Str::upper(Str::random(10)),
            'subtotal' => 100,
            'shipping_total' => 0,
            'discount_total' => 0,
            'total' => 100,
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 50,
            'quantity' => 2,
            'line_total' => 100,
        ]);
        $recordStatus = match ($paymentStatus) {
            'paid' => 'paid',
            'refunded' => 'refunded',
            default => 'pending',
        };
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => 100,
            'gateway' => 'cod',
            'status' => $recordStatus,
        ]);
        InventoryMovement::create([
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'order_placed',
            'quantity' => 2,
            'created_at' => now(),
        ]);

        return compact('user', 'product', 'inventory', 'address', 'order', 'item', 'payment');
    }

    public function test_customer_can_view_owned_order_details(): void
    {
        $context = $this->context();
        Shipping::create([
            'order_id' => $context['order']->id,
            'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-OWNED',
            'status' => 'pending',
        ]);

        $this->actingAs($context['user'])
            ->get(route('orders.show', $context['order']))
            ->assertOk()
            ->assertSee($context['order']->order_number)
            ->assertSee($context['product']->name)
            ->assertSee($context['address']->address_line)
            ->assertSee('Pathao')
            ->assertSee('TRACK-OWNED')
            ->assertSee('cod')
            ->assertSee('pending')
            ->assertSee('unpaid')
            ->assertSee(__('Cancel order'));
    }

    public function test_customer_order_list_links_to_order_details(): void
    {
        $context = $this->context();

        $this->actingAs($context['user'])
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($context['order']->order_number)
            ->assertSee(route('orders.show', $context['order']));
    }

    public function test_customer_cannot_view_or_cancel_another_users_order(): void
    {
        $context = $this->context();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('orders.show', $context['order']))->assertNotFound();
        $this->actingAs($intruder)->post(route('orders.cancel', $context['order']))->assertNotFound();

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'pending']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
        $this->assertDatabaseMissing('inventory_movements', [
            'order_id' => $context['order']->id,
            'type' => 'order_cancelled',
        ]);
    }

    public function test_customer_cancellation_restores_stock_exactly_once(): void
    {
        $context = $this->context();

        $this->actingAs($context['user'])
            ->post(route('orders.cancel', $context['order']))
            ->assertRedirect(route('orders.show', $context['order']))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', [
            'id' => $context['order']->id,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
        ]);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseHas('inventory_movements', [
            'order_id' => $context['order']->id,
            'product_id' => $context['product']->id,
            'type' => 'order_cancelled',
            'quantity' => 2,
        ]);

        $this->actingAs($context['user'])
            ->post(route('orders.cancel', $context['order']))
            ->assertRedirect(route('orders.show', $context['order']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseCount('inventory_movements', 2);
    }

    public function test_customer_cannot_cancel_a_pending_paid_order(): void
    {
        $context = $this->context('pending', 'paid');

        $this->actingAs($context['user'])
            ->post(route('orders.cancel', $context['order']))
            ->assertRedirect(route('orders.show', $context['order']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'pending', 'payment_status' => 'paid']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'paid']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
        $this->assertDatabaseMissing('inventory_movements', ['order_id' => $context['order']->id, 'type' => 'order_cancelled']);
    }

    public function test_customer_cannot_cancel_a_processing_order(): void
    {
        $context = $this->context('processing');

        $this->actingAs($context['user'])
            ->post(route('orders.cancel', $context['order']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'processing']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
    }

    public function test_admin_can_move_a_pending_order_to_processing(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.orders.transition', $context['order']), ['status' => 'processing'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'processing']);
    }

    public function test_admin_can_cancel_a_pending_order_and_restore_stock(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.orders.transition', $context['order']), ['status' => 'cancelled'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseHas('inventory_movements', ['order_id' => $context['order']->id, 'type' => 'order_cancelled', 'quantity' => 2]);
    }

    public function test_admin_can_cancel_a_processing_paid_order_and_refund_once(): void
    {
        $context = $this->context('processing', 'paid');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $context['order']))
            ->post(route('admin.orders.transition', $context['order']), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.show', $context['order']))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'cancelled', 'payment_status' => 'refunded']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'refunded']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);

        $this->actingAs($admin)
            ->post(route('admin.orders.transition', $context['order']), ['status' => 'cancelled'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseCount('inventory_movements', 2);
    }

    public function test_admin_rejects_invalid_order_transitions_without_mutation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            ['shipped', 'unpaid', 'processing'],
            ['shipped', 'unpaid', 'cancelled'],
            ['completed', 'paid', 'processing'],
            ['completed', 'paid', 'cancelled'],
        ] as [$orderStatus, $paymentStatus, $target]) {
            $context = $this->context($orderStatus, $paymentStatus);

            $this->actingAs($admin)
                ->post(route('admin.orders.transition', $context['order']), ['status' => $target])
                ->assertSessionHas('error');

            $this->assertDatabaseHas('orders', [
                'id' => $context['order']->id,
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
            ]);
            $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
            $this->assertDatabaseMissing('inventory_movements', ['order_id' => $context['order']->id, 'type' => 'order_cancelled']);
        }
    }

    public function test_admin_order_detail_exposes_workflow_actions(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $context['order']))
            ->assertOk()
            ->assertSee($context['order']->order_number)
            ->assertSee(__('Start processing'))
            ->assertSee(__('Cancel order'));
    }

    public function test_admin_order_list_links_to_order_details(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($context['order']->order_number)
            ->assertSee(route('admin.orders.show', $context['order']));
    }

    public function test_non_admin_cannot_manage_orders(): void
    {
        $context = $this->context();

        $this->actingAs($context['user'])
            ->post(route('admin.orders.transition', $context['order']), ['status' => 'processing'])
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'pending']);
    }
}
