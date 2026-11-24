<?php

namespace Tests\Feature\Shipping;

use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Shipping\Models\Shipping;
use Illuminate\Support\Str;
use Tests\Feature\Identity\IdentityTestCase;

class ShippingLifecycleTest extends IdentityTestCase
{
    private function order(string $status = 'pending', string $paymentStatus = 'unpaid'): array
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-SHP-'.Str::upper(Str::random(8)),
            'total' => 100,
            'status' => $status,
            'payment_status' => $paymentStatus,
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

        return compact('order', 'payment');
    }

    private function shipment(string $shipmentStatus = 'pending', string $orderStatus = 'pending', string $paymentStatus = 'unpaid'): array
    {
        $context = $this->order($orderStatus, $paymentStatus);
        $context['shipment'] = Shipping::create([
            'order_id' => $context['order']->id,
            'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-'.Str::upper(Str::random(6)),
            'status' => $shipmentStatus,
        ]);

        return $context;
    }

    public function test_admin_can_create_a_shipment_for_a_non_cancelled_order(): void
    {
        $context = $this->order();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.shipping.index'))
            ->post(route('admin.shipping.store'), [
                'order_id' => $context['order']->id,
                'carrier' => 'Pathao',
                'tracking_number' => 'TRACK-NEW',
            ])
            ->assertRedirect(route('admin.shipping.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('shippings', [
            'order_id' => $context['order']->id,
            'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-NEW',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'pending']);
    }

    public function test_duplicate_active_shipment_is_rejected(): void
    {
        $context = $this->order();
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = [
            'order_id' => $context['order']->id,
            'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-ONE',
        ];

        $this->actingAs($admin)->post(route('admin.shipping.store'), $payload)->assertSessionHas('status');
        $shipment = Shipping::where('order_id', $context['order']->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.shipping.store'), [...$payload, 'tracking_number' => 'TRACK-TWO'])
            ->assertSessionHas('error');
        $this->assertDatabaseCount('shippings', 1);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $shipment), ['status' => 'shipped'])
            ->assertSessionHas('status');

        $this->actingAs($admin)
            ->post(route('admin.shipping.store'), [...$payload, 'tracking_number' => 'TRACK-THREE'])
            ->assertSessionHas('error');
        $this->assertDatabaseCount('shippings', 1);
    }

    public function test_shipment_cannot_be_created_for_a_cancelled_order(): void
    {
        $context = $this->order('cancelled');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.store'), [
                'order_id' => $context['order']->id,
                'carrier' => 'Pathao',
                'tracking_number' => 'TRACK-CANCELLED',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('shippings', ['order_id' => $context['order']->id]);
    }

    public function test_marking_shipment_shipped_syncs_the_order(): void
    {
        $context = $this->shipment();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'shipped'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('shippings', ['id' => $context['shipment']->id, 'status' => 'shipped']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'shipped', 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'pending']);
    }

    public function test_marking_shipment_delivered_completes_order_and_collects_cod(): void
    {
        $context = $this->shipment('shipped', 'shipped');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'delivered'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('shippings', ['id' => $context['shipment']->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'completed', 'payment_status' => 'paid']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'paid']);
    }

    public function test_delivery_preserves_an_already_paid_payment(): void
    {
        $context = $this->shipment('shipped', 'shipped', 'paid');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'delivered'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'completed', 'payment_status' => 'paid']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'paid']);
    }

    public function test_delivery_does_not_collect_cod_again_when_another_payment_already_covers_the_total(): void
    {
        $context = $this->shipment('shipped', 'shipped');
        $admin = User::factory()->create(['is_admin' => true]);
        Payment::create([
            'order_id' => $context['order']->id,
            'amount' => 100,
            'gateway' => 'bkash',
            'status' => 'paid',
        ]);
        $context['order']->update(['payment_status' => 'paid']);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'delivered'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'paid']);
    }

    public function test_delivery_does_not_mark_a_pending_non_cod_payment_as_paid(): void
    {
        $context = $this->shipment('shipped', 'shipped');
        $context['payment']->update(['gateway' => 'bkash']);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'delivered'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('shippings', ['id' => $context['shipment']->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'completed', 'payment_status' => 'unpaid']);
    }

    public function test_delivery_with_an_uncovered_cod_amount_does_not_mark_the_order_paid(): void
    {
        $context = $this->shipment('shipped', 'shipped');
        $context['payment']->update(['amount' => 40]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'delivered'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'completed', 'payment_status' => 'partially_paid']);
    }

    public function test_invalid_shipping_transitions_do_not_mutate_records(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            ['pending', 'pending', 'unpaid', 'delivered'],
            ['shipped', 'shipped', 'unpaid', 'shipped'],
            ['delivered', 'completed', 'paid', 'shipped'],
            ['delivered', 'completed', 'paid', 'delivered'],
        ] as [$shipmentStatus, $orderStatus, $paymentStatus, $target]) {
            $context = $this->shipment($shipmentStatus, $orderStatus, $paymentStatus);

            $this->actingAs($admin)
                ->post(route('admin.shipping.transition', $context['shipment']), ['status' => $target])
                ->assertSessionHas('error');

            $this->assertDatabaseHas('shippings', ['id' => $context['shipment']->id, 'status' => $shipmentStatus]);
            $this->assertDatabaseHas('orders', [
                'id' => $context['order']->id,
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
            ]);
        }
    }

    public function test_cancelled_order_shipment_cannot_be_shipped(): void
    {
        $context = $this->shipment('pending', 'cancelled');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.transition', $context['shipment']), ['status' => 'shipped'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('shippings', ['id' => $context['shipment']->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'cancelled']);
    }

    public function test_shipment_creation_requires_carrier_and_tracking_number(): void
    {
        $context = $this->order();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.shipping.store'), ['order_id' => $context['order']->id])
            ->assertSessionHasErrors(['carrier', 'tracking_number']);

        $this->assertDatabaseMissing('shippings', ['order_id' => $context['order']->id]);
    }

    public function test_admin_shipping_page_exposes_creation_and_transition_actions(): void
    {
        $this->shipment();
        $this->shipment('shipped', 'shipped');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.shipping.index'))
            ->assertOk()
            ->assertSee(__('Create shipment'))
            ->assertSee(__('Mark shipped'))
            ->assertSee(__('Mark delivered'));
    }

    public function test_non_admin_cannot_manage_shipping(): void
    {
        $context = $this->order();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.shipping.store'), [
                'order_id' => $context['order']->id,
                'carrier' => 'Pathao',
                'tracking_number' => 'TRACK-NO',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('shippings', ['order_id' => $context['order']->id]);
    }
}
