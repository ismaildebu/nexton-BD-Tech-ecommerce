<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Product\Models\Product;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Shipping\Services\ShippingService;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class VendorOperationsTest extends IdentityTestCase
{
    public function test_vendor_navigation_drawer_has_accessible_focus_wiring(): void
    {
        $owner = User::factory()->create();
        $this->makeVendor($owner);

        $response = $this->actingAs($owner)->get(route('vendor.inventory.index'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());
        $drawer = $xpath->query('//*[@id="vendor-navigation"]')->item(0);

        $this->assertNotNull($drawer);
        $this->assertSame('vendorDrawer', $drawer->getAttribute('x-ref'));
        $this->assertSame('-1', $drawer->getAttribute('tabindex'));
        $response->assertSee('x-ref="vendorDrawerOpen"', false)
            ->assertSee('aria-controls="vendor-navigation"', false)
            ->assertSee('$refs.vendorDrawer.focus()', false);
    }

    public function test_vendor_can_adjust_own_inventory_with_auditable_movements(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $product = $this->makeProduct($vendor, 'Own Product');
        $otherProduct = $this->makeProduct($this->makeVendor(User::factory()->create()), 'Other Product');
        Inventory::create(['product_id' => $product->id, 'quantity' => 5, 'low_stock_threshold' => 2]);

        $this->actingAs($owner)->post(route('vendor.inventory.adjust'), [
            'product_id' => $product->id, 'direction' => 'in', 'quantity' => 3, 'note' => 'Restocked',
        ])->assertRedirect(route('vendor.inventory.index'));

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 8]);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id, 'user_id' => $owner->id, 'type' => 'adjustment_in',
            'quantity' => 3, 'note' => 'Restocked',
        ]);

        $this->actingAs($owner)->post(route('vendor.inventory.adjust'), [
            'product_id' => $product->id, 'direction' => 'out', 'quantity' => 20,
        ])->assertSessionHas('error');
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 8]);
        $this->assertDatabaseCount('inventory_movements', 1);

        $this->actingAs($owner)->get(route('vendor.inventory.index'))
            ->assertOk()->assertSee('Own Product')->assertDontSee('Other Product');
    }

    public function test_inventory_validation_restores_only_the_submitted_product_row(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $submittedProduct = $this->makeProduct($vendor, 'Submitted Product');
        $otherProduct = $this->makeProduct($vendor, 'Other Row Product');

        $this->actingAs($owner)
            ->from(route('vendor.inventory.index'))
            ->post(route('vendor.inventory.adjust'), [
                'product_id' => $submittedProduct->id,
                'direction' => 'in',
                'quantity' => 0,
                'note' => 'Row-specific note',
            ])
            ->assertSessionHasErrors('quantity');

        $response = $this->actingAs($owner)->get(route('vendor.inventory.index'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());
        $submittedQuantity = $xpath->query('//*[@id="quantity-'.$submittedProduct->id.'"]')->item(0);
        $otherQuantity = $xpath->query('//*[@id="quantity-'.$otherProduct->id.'"]')->item(0);

        $this->assertSame('0', $submittedQuantity->getAttribute('value'));
        $this->assertSame('', $otherQuantity->getAttribute('value'));
        $this->assertSame('true', $submittedQuantity->getAttribute('aria-invalid'));
        $this->assertSame('', $otherQuantity->getAttribute('aria-invalid'));
        $this->assertSame('Row-specific note', $xpath->query('//*[@id="note-'.$submittedProduct->id.'"]')->item(0)->getAttribute('value'));
        $this->assertSame('', $xpath->query('//*[@id="note-'.$otherProduct->id.'"]')->item(0)->getAttribute('value'));
    }

    public function test_vendor_cannot_adjust_another_vendors_inventory(): void
    {
        $owner = User::factory()->create();
        $other = $this->makeVendor(User::factory()->create());
        $product = $this->makeProduct($other, 'Protected Stock');

        $this->actingAs($owner)->post(route('vendor.inventory.adjust'), [
            'product_id' => $product->id, 'direction' => 'in', 'quantity' => 1,
        ])->assertForbidden();

        $this->assertDatabaseMissing('inventories', ['product_id' => $product->id]);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_order_list_and_detail_are_scoped_through_item_product_vendor(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();
        $customer = User::factory()->create();
        $vendorA = $this->makeVendor($ownerA);
        $vendorB = $this->makeVendor($ownerB);
        $productA = $this->makeProduct($vendorA, 'Vendor A Item');
        $productB = $this->makeProduct($vendorB, 'Vendor B Secret Item');
        $mixed = $this->makeOrder($customer, 'ORD-MIXED', [$productA, $productB]);
        $onlyB = $this->makeOrder($customer, 'ORD-ONLY-B', [$productB]);
        $paymentB = $mixed->payments()->where('vendor_id', $vendorB->id)->firstOrFail();
        $paymentB->update(['gateway' => 'B-SECRET-GATEWAY']);
        app(ShippingService::class)->create($mixed->id, 'B-SECRET-CARRIER', 'B-SECRET-TRACK', $vendorB->id);

        $this->actingAs($ownerA)->get(route('vendor.orders.index'))
            ->assertOk()->assertSee('ORD-MIXED')->assertDontSee('ORD-ONLY-B');
        $detail = $this->actingAs($ownerA)->get(route('vendor.orders.show', $mixed))
            ->assertOk()->assertSee('Vendor A Item')->assertDontSee('Vendor B Secret Item')
            ->assertSee('Payment (read only)')->assertSee('৳ 100.00')
            ->assertDontSee('B-SECRET-GATEWAY')->assertDontSee('B-SECRET-CARRIER')->assertDontSee('B-SECRET-TRACK');
        $xpath = $this->htmlXPath($detail->getContent());
        $shipmentForms = $xpath->query('//form[@action="'.route('vendor.orders.shipping.store', $mixed).'"]');
        $this->assertCount(1, $shipmentForms);
        $this->assertCount(1, $xpath->query('.//input[@name="_token"]', $shipmentForms->item(0)));
        $this->actingAs($ownerA)->get(route('vendor.orders.show', $onlyB))->assertNotFound();
        $this->actingAs($ownerA)->post(route('vendor.orders.shipping.store', $onlyB), [
            'carrier' => 'Courier', 'tracking_number' => 'TRACK-1',
        ])->assertNotFound();
    }

    public function test_vendor_can_create_and_mark_its_shipment_shipped(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $product = $this->makeProduct($vendor, 'Shippable Item');
        $order = $this->makeOrder(User::factory()->create(), 'ORD-SHIP', [$product]);

        $this->actingAs($owner)->post(route('vendor.orders.shipping.store', $order), [
            'carrier' => 'Pathao', 'tracking_number' => 'TRACK-123',
        ])->assertRedirect(route('vendor.orders.show', $order));
        $shipping = Shipping::firstOrFail();
        $this->assertDatabaseHas('shippings', [
            'id' => $shipping->id, 'order_id' => $order->id, 'vendor_id' => $vendor->id, 'carrier' => 'Pathao',
            'tracking_number' => 'TRACK-123', 'status' => 'pending',
        ]);

        $this->actingAs($owner)->post(route('vendor.orders.shipping.ship', [$order, $shipping]))
            ->assertRedirect(route('vendor.orders.show', $order));
        $this->assertDatabaseHas('shippings', ['id' => $shipping->id, 'status' => 'shipped']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
    }

    public function test_vendor_cannot_transition_another_vendors_order_or_shipment(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $product = $this->makeProduct($vendor, 'Protected Item');
        $order = $this->makeOrder(User::factory()->create(), 'ORD-PROTECTED', [$product]);
        $shipping = Shipping::create(['order_id' => $order->id, 'carrier' => 'Courier', 'tracking_number' => 'X', 'status' => 'pending']);

        $this->actingAs($intruder)->post(route('vendor.orders.shipping.ship', [$order, $shipping]))->assertNotFound();
        $this->assertDatabaseHas('shippings', ['id' => $shipping->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);
    }

    public function test_mixed_vendor_fulfillment_is_isolated_and_completes_only_after_all_vendors_deliver(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();
        $vendorA = $this->makeVendor($ownerA);
        $vendorB = $this->makeVendor($ownerB);
        $productA = $this->makeProduct($vendorA, 'Mixed Vendor A');
        $productB = $this->makeProduct($vendorB, 'Mixed Vendor B');
        $order = $this->makeOrder(User::factory()->create(), 'ORD-MIXED-FULFILLMENT', [$productA, $productB]);

        $this->actingAs($ownerA)->post(route('vendor.orders.shipping.store', $order), [
            'carrier' => 'Courier A', 'tracking_number' => 'TRACK-A',
        ])->assertRedirect(route('vendor.orders.show', $order));
        $this->actingAs($ownerB)->post(route('vendor.orders.shipping.store', $order), [
            'carrier' => 'Courier B', 'tracking_number' => 'TRACK-B',
        ])->assertRedirect(route('vendor.orders.show', $order));

        $shipmentA = Shipping::where('order_id', $order->id)->where('vendor_id', $vendorA->id)->firstOrFail();
        $shipmentB = Shipping::where('order_id', $order->id)->where('vendor_id', $vendorB->id)->firstOrFail();

        $this->actingAs($ownerA)->post(route('vendor.orders.shipping.ship', [$order, $shipmentA]))
            ->assertRedirect(route('vendor.orders.show', $order));
        $this->actingAs($ownerA)->post(route('vendor.orders.shipping.deliver', [$order, $shipmentA]))
            ->assertRedirect(route('vendor.orders.show', $order));

        $this->assertDatabaseHas('shippings', ['id' => $shipmentA->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('shippings', ['id' => $shipmentB->id, 'status' => 'pending']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'vendor_id' => $vendorA->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'vendor_id' => $vendorB->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped', 'payment_status' => 'partially_paid']);

        $this->actingAs($ownerA)->post(route('vendor.orders.shipping.deliver', [$order, $shipmentB]))->assertNotFound();
        $this->assertDatabaseHas('shippings', ['id' => $shipmentB->id, 'status' => 'pending']);

        $this->actingAs($ownerB)->post(route('vendor.orders.shipping.ship', [$order, $shipmentB]))
            ->assertRedirect(route('vendor.orders.show', $order));
        $this->actingAs($ownerB)->post(route('vendor.orders.shipping.deliver', [$order, $shipmentB]))
            ->assertRedirect(route('vendor.orders.show', $order));

        $this->assertDatabaseHas('shippings', ['id' => $shipmentB->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed', 'payment_status' => 'paid']);
    }

    private function makeVendor(User $owner): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id, 'vendor_code' => 'VND-OPS-'.uniqid(),
            'business_name' => 'Operations Shop', 'verification_status' => 'approved', 'status' => 'active',
        ]);
    }

    private function makeProduct(Vendor $vendor, string $name): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id, 'name' => $name, 'slug' => str($name)->slug().'-'.uniqid(),
            'price' => '100.00', 'status' => 'approved', 'is_active' => true,
        ]);
    }

    private function makeOrder(User $customer, string $number, array $products): Order
    {
        $order = Order::create([
            'user_id' => $customer->id, 'order_number' => $number, 'subtotal' => 100 * count($products),
            'shipping_total' => 0, 'discount_total' => 0, 'total' => 100 * count($products),
            'status' => 'processing', 'payment_status' => 'unpaid',
            'shipping_address' => ['full_name' => $customer->name, 'phone' => '+8801700000000', 'address_line' => 'Test Road'],
        ]);

        foreach ($products as $product) {
            $order->items()->create([
                'product_id' => $product->id, 'vendor_id' => $product->vendor_id, 'name' => $product->name,
                'unit_price' => '100.00', 'quantity' => 1, 'line_total' => '100.00',
            ]);
        }

        app(PaymentService::class)->recordForOrder($order);

        return $order;
    }
}
