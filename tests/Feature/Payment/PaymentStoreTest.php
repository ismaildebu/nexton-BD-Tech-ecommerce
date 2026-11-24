<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class PaymentStoreTest extends IdentityTestCase
{
    private function vendor(): Vendor
    {
        return Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-PAY-'.uniqid(),
            'business_name' => 'Payment Shop',
            'verification_status' => 'approved',
            'status' => 'active',
        ]);
    }

    private function order(User $user, string $number, Vendor $vendor, int $amount = 100): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => $number,
            'subtotal' => $amount,
            'total' => $amount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Payment Product',
            'slug' => 'payment-product-'.uniqid(),
            'price' => $amount,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'name' => $product->name,
            'unit_price' => $amount,
            'quantity' => 1,
            'line_total' => $amount,
        ]);

        return $order;
    }

    public function test_customer_cannot_create_a_payment_for_own_order(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, 'ORD-T001', $this->vendor());

        $this->actingAs($user)
            ->post(route('payments.store'), ['order_id' => $order->id, 'amount' => 1, 'gateway' => 'bkash'])
            ->assertForbidden();

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_user_cannot_record_payment_for_another_users_order(): void
    {
        $intruder = User::factory()->create();
        $order = $this->order(User::factory()->create(), 'ORD-T002', $this->vendor());

        $this->actingAs($intruder)
            ->post(route('payments.store'), ['order_id' => $order->id, 'amount' => 100])
            ->assertForbidden();

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_customer_payment_history_renders_transaction_tooltips_without_alpine_expressions(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, 'ORD-T002A', $this->vendor());
        Payment::create([
            'order_id' => $order->id,
            'amount' => 100,
            'gateway' => 'bkash',
            'status' => 'paid',
            'transaction_id' => 'TRX-STATIC-001',
        ]);

        $response = $this->actingAs($user)->get(route('payments.index'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());

        $this->assertCount(1, $xpath->query('//td[@title="TRX-STATIC-001" and normalize-space(.)="TRX-STATIC-001"]'));
    }

    public function test_admin_payment_creation_is_vendor_scoped_and_idempotent(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor();
        $order = $this->order(User::factory()->create(), 'ORD-T003', $vendor);
        $payload = ['order_id' => $order->id, 'vendor_id' => $vendor->id, 'amount' => 1, 'gateway' => 'bkash'];

        $this->actingAs($admin)->post(route('payments.store'), $payload)
            ->assertRedirect(route('payments.index'));
        $this->actingAs($admin)->post(route('payments.store'), $payload)
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'amount' => 100,
            'gateway' => 'bkash',
            'status' => 'pending',
        ]);
    }

    public function test_admin_must_select_a_vendor_for_a_mixed_order_payment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendorA = $this->vendor();
        $vendorB = $this->vendor();
        $order = $this->order(User::factory()->create(), 'ORD-T004', $vendorA);
        $product = Product::create([
            'vendor_id' => $vendorB->id,
            'name' => 'Second Vendor Product',
            'slug' => 'second-vendor-product-'.uniqid(),
            'price' => 100,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'vendor_id' => $vendorB->id,
            'name' => $product->name,
            'unit_price' => 100,
            'quantity' => 1,
            'line_total' => 100,
        ]);
        $order->update(['subtotal' => 200, 'total' => 200]);

        $this->actingAs($admin)->post(route('payments.store'), ['order_id' => $order->id])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);

        $this->actingAs($admin)->post(route('payments.store'), [
            'order_id' => $order->id, 'vendor_id' => $vendorA->id, 'gateway' => 'cod',
        ])->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id, 'vendor_id' => $vendorA->id, 'amount' => 100,
        ]);
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id, 'vendor_id' => $vendorB->id]);
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }
}
