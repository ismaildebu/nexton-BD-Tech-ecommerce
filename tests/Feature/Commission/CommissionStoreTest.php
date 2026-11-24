<?php

namespace Tests\Feature\Commission;

use App\Models\User;
use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Services\CommissionService;
use App\Modules\Order\Models\Order;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class CommissionStoreTest extends IdentityTestCase
{
    private function vendor(User $owner, string $code): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => $code,
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function orderWithItems(Vendor $vendor, string $number, array $lineTotals): Order
    {
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => $number,
            'subtotal' => array_sum($lineTotals),
            'shipping_total' => 0,
            'discount_total' => 0,
            'total' => array_sum($lineTotals),
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        foreach ($lineTotals as $index => $lineTotal) {
            $product = Product::create([
                'vendor_id' => $vendor->id,
                'name' => 'Commission Product '.($index + 1),
                'slug' => str($number)->slug().'-product-'.($index + 1),
                'price' => $lineTotal,
                'status' => 'approved',
                'is_active' => true,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'name' => $product->name,
                'unit_price' => $lineTotal,
                'quantity' => 1,
                'line_total' => $lineTotal,
            ]);
        }

        return $order;
    }

    public function test_vendor_owner_cannot_record_own_commission(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->vendor($owner, 'VND-COM001');

        $this->actingAs($owner)
            ->post(route('commissions.store'), ['vendor_id' => $vendor->id, 'amount' => 10, 'rate' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_user_cannot_record_commission_for_another_vendor(): void
    {
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM002');

        $this->actingAs(User::factory()->create())
            ->post(route('commissions.store'), ['vendor_id' => $vendor->id, 'amount' => 10])
            ->assertForbidden();

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_admin_can_record_manual_commission_without_an_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM003');

        $this->actingAs($admin)
            ->post(route('commissions.store'), ['vendor_id' => $vendor->id, 'amount' => 12.34, 'rate' => 5])
            ->assertRedirect(route('admin.commissions.index'))
            ->assertSessionHas('status');

        $commission = Commission::firstOrFail();
        $this->assertSame($vendor->id, $commission->vendor_id);
        $this->assertNull($commission->order_id);
        $this->assertSame('12.34', $commission->amount);
        $this->assertSame('5.00', $commission->rate);
    }

    public function test_admin_order_linked_rate_derives_amount_from_only_the_vendors_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM004');
        $otherVendor = $this->vendor(User::factory()->create(), 'VND-COM005');
        $order = $this->orderWithItems($vendor, 'ORD-COM-004', [50.25, 20.10]);
        $this->orderWithItems($otherVendor, 'ORD-COM-005', [999]);

        $otherProduct = Product::where('vendor_id', $otherVendor->id)->firstOrFail();
        $order->items()->create([
            'product_id' => $otherProduct->id,
            'name' => $otherProduct->name,
            'unit_price' => 999,
            'quantity' => 1,
            'line_total' => 999,
        ]);
        $order->update(['subtotal' => 1069.35, 'total' => 1069.35]);

        $this->actingAs($admin)
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'rate' => 12.5,
            ])
            ->assertRedirect(route('admin.commissions.index'))
            ->assertSessionHas('status');

        $commission = Commission::firstOrFail();
        $this->assertSame($order->id, $commission->order_id);
        $this->assertSame('8.79', $commission->amount);
        $this->assertSame('12.50', $commission->rate);
    }

    public function test_admin_can_record_an_order_linked_fixed_amount_within_vendor_total(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM006');
        $order = $this->orderWithItems($vendor, 'ORD-COM-006', [80]);

        $this->actingAs($admin)
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 20,
            ])
            ->assertRedirect(route('admin.commissions.index'));

        $this->assertDatabaseHas('commissions', [
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'amount' => 20,
            'rate' => 0,
        ]);
    }

    public function test_order_linked_rate_rejects_a_mismatched_amount(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM007');
        $order = $this->orderWithItems($vendor, 'ORD-COM-007', [100]);

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'rate' => 10,
                'amount' => 11,
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_order_linked_amount_without_rate_cannot_exceed_vendor_total(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM008');
        $order = $this->orderWithItems($vendor, 'ORD-COM-008', [100]);

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 100.01,
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_order_linked_commissions_are_capped_at_the_vendor_total_in_aggregate(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM014');
        $order = $this->orderWithItems($vendor, 'ORD-COM-014', [100]);

        $this->actingAs($admin)
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 60,
            ])
            ->assertRedirect(route('admin.commissions.index'));

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 40.01,
            ])
            ->assertRedirect(route('admin.commissions.index'))
            ->assertSessionHas('error', __('An order-linked commission cannot exceed the vendor order total.'));

        $this->assertDatabaseCount('commissions', 1);
        $this->assertSame(60.0, (float) Commission::where('vendor_id', $vendor->id)
            ->where('order_id', $order->id)
            ->sum('amount'));
    }

    public function test_order_linked_commissions_can_complete_the_exact_base_but_a_replay_writes_no_row(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM015');
        $order = $this->orderWithItems($vendor, 'ORD-COM-015', [100.01]);

        $this->actingAs($admin)
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 60.01,
            ])
            ->assertRedirect(route('admin.commissions.index'));

        $this->actingAs($admin)
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'rate' => 40,
            ])
            ->assertRedirect(route('admin.commissions.index'));

        $this->assertDatabaseCount('commissions', 2);
        $this->assertSame(100.01, round((float) Commission::where('vendor_id', $vendor->id)
            ->where('order_id', $order->id)
            ->sum('amount'), 2));

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'rate' => 40,
            ])
            ->assertRedirect(route('admin.commissions.index'))
            ->assertSessionHas('error', __('An order-linked commission cannot exceed the vendor order total.'));

        $this->assertDatabaseCount('commissions', 2);
        $this->assertSame(100.01, round((float) Commission::where('vendor_id', $vendor->id)
            ->where('order_id', $order->id)
            ->sum('amount'), 2));
    }

    public function test_order_linked_commission_rejects_an_order_without_vendor_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM009');
        $otherVendor = $this->vendor(User::factory()->create(), 'VND-COM010');
        $order = $this->orderWithItems($otherVendor, 'ORD-COM-010', [100]);

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), [
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'amount' => 10,
            ])
            ->assertSessionHasErrors('order_id');

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_manual_commission_requires_an_amount_even_when_rate_is_supplied(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM011');

        $this->actingAs($admin)
            ->from(route('admin.commissions.index'))
            ->post(route('commissions.store'), ['vendor_id' => $vendor->id, 'rate' => 5])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('commissions', 0);
    }

    public function test_system_process_can_derive_an_order_linked_commission(): void
    {
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM012');
        $order = $this->orderWithItems($vendor, 'ORD-COM-012', [200]);

        $commission = app(CommissionService::class)->record($vendor->id, null, 7.5, $order->id);

        $this->assertSame($order->id, $commission->order_id);
        $this->assertSame('15.00', $commission->amount);
        $this->assertDatabaseHas('commissions', ['id' => $commission->id, 'vendor_id' => $vendor->id]);
    }

    public function test_admin_commission_page_exposes_the_creation_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-COM013');

        $this->actingAs($admin)
            ->get(route('admin.commissions.index'))
            ->assertOk()
            ->assertSee('Record commission')
            ->assertSee('Manual commission')
            ->assertSee($vendor->business_name)
            ->assertSee(route('commissions.store'), false);
    }
}
