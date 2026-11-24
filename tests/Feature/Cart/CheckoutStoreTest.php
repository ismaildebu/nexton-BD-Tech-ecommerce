<?php

namespace Tests\Feature\Cart;

use App\Models\Address;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Services\CouponService;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Identity\IdentityTestCase;

class CheckoutStoreTest extends IdentityTestCase
{
    private function context(int $stock = 5): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-CHK-'.uniqid(),
            'business_name' => 'Checkout Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Checkout Product',
            'slug' => 'checkout-product-'.uniqid(),
            'price' => 25.50,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $inventory = Inventory::create(['product_id' => $product->id, 'quantity' => $stock]);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);
        $address = Address::factory()->for($user)->create();

        return compact('user', 'vendor', 'product', 'inventory', 'cart', 'address');
    }

    private function payload(Address $address, ?string $key = null): array
    {
        return [
            'address_id' => $address->id,
            'idempotency_key' => $key ?? (string) Str::uuid(),
            'total' => 1,
        ];
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ], $overrides));
    }

    public function test_checkout_creates_order_items_and_deducts_stock_atomically(): void
    {
        $context = $this->context();
        $user = $context['user'];
        $address = $context['address'];

        $this->actingAs($user)
            ->post(route('checkout.store'), $this->payload($address))
            ->assertRedirect(route('orders.index'));

        $order = Order::where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'address_id' => $address->id,
            'subtotal' => 51,
            'total' => 51,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        $this->assertSame($address->address_line, $order->shipping_address['address_line']);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $context['product']->id,
            'vendor_id' => $context['vendor']->id,
            'name' => $context['product']->name,
            'unit_price' => 25.50,
            'quantity' => 2,
            'line_total' => 51,
        ]);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
        $this->assertDatabaseHas('inventory_movements', [
            'order_id' => $order->id,
            'product_id' => $context['product']->id,
            'type' => 'order_placed',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => 51,
            'gateway' => 'cod',
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $context['cart']->id]);
    }

    public function test_checkout_splits_a_discounted_mixed_vendor_total_into_scoped_payments(): void
    {
        $context = $this->context();
        $vendorB = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-CHK-'.uniqid(),
            'business_name' => 'Second Checkout Shop',
            'verification_status' => 'approved',
            'status' => 'active',
        ]);
        $productB = Product::create([
            'vendor_id' => $vendorB->id,
            'name' => 'Second Checkout Product',
            'slug' => 'second-checkout-product-'.uniqid(),
            'price' => 25.50,
            'status' => 'approved',
            'is_active' => true,
        ]);
        Inventory::create(['product_id' => $productB->id, 'quantity' => 5]);
        CartItem::create(['cart_id' => $context['cart']->id, 'product_id' => $productB->id, 'quantity' => 1]);
        $this->coupon();

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertRedirect(route('orders.index'));

        $order = Order::where('user_id', $context['user']->id)->firstOrFail();
        $this->assertSame('68.85', $order->total);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'vendor_id' => $context['vendor']->id]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'vendor_id' => $vendorB->id]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id, 'vendor_id' => $context['vendor']->id, 'amount' => 45.90, 'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id, 'vendor_id' => $vendorB->id, 'amount' => 22.95, 'status' => 'pending',
        ]);
        $this->assertSame(2, Payment::where('order_id', $order->id)->count());
        $this->assertSame(68.85, (float) Payment::where('order_id', $order->id)->sum('amount'));
    }

    public function test_checkout_rejects_a_product_after_its_vendor_is_suspended(): void
    {
        $context = $this->context();
        $context['product']->vendor->update([
            'status' => 'suspended',
            'verification_status' => 'suspended',
        ]);

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $context['cart']->id]);
    }

    public function test_checkout_is_idempotent_for_the_same_key(): void
    {
        $context = $this->context();
        $key = (string) Str::uuid();
        $payload = $this->payload($context['address'], $key);

        $this->actingAs($context['user'])->post(route('checkout.store'), $payload)->assertRedirect();
        $this->actingAs($context['user'])->post(route('checkout.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', ['gateway' => 'cod', 'amount' => 51, 'status' => 'pending']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $context = $this->context();
        $context['cart']->items()->delete();

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['user_id' => $context['user']->id]);
    }

    public function test_checkout_rejects_another_users_address(): void
    {
        $context = $this->context();
        $otherAddress = Address::factory()->create();

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $this->payload($otherAddress))
            ->assertSessionHasErrors('address_id');

        $this->assertDatabaseMissing('orders', ['user_id' => $context['user']->id]);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
    }

    public function test_insufficient_stock_rolls_back_the_order(): void
    {
        $context = $this->context(1);

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['user_id' => $context['user']->id]);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 1]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $context['cart']->id, 'quantity' => 2]);
    }

    public function test_unapproved_product_cannot_be_checked_out(): void
    {
        $context = $this->context();
        $context['product']->update(['status' => 'pending']);

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', ['user_id' => $context['user']->id]);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
    }

    #[DataProvider('orderDiscounts')]
    public function test_checkout_persists_server_discount_and_ignores_tampered_amounts(string $type, string $value, string $discount, string $total): void
    {
        $context = $this->context();
        $this->coupon(['type' => $type, 'value' => $value]);
        $payload = array_merge($this->payload($context['address']), [
            'discount_total' => 50,
            'total' => 1,
            'coupon_code' => 'CLIENT_SUPPLIED',
        ]);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.index'))
            ->assertSessionMissing(CouponService::SESSION_KEY);

        $order = Order::where('user_id', $context['user']->id)->firstOrFail();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 51,
            'discount_total' => $discount,
            'total' => $total,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => $total,
            'gateway' => 'cod',
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $context['cart']->id]);
    }

    public static function orderDiscounts(): array
    {
        return [
            'percentage' => ['percent', '10', '5.10', '45.90'],
            'fixed amount' => ['fixed', '10.50', '10.50', '40.50'],
        ];
    }

    public function test_checkout_ignores_a_client_supplied_coupon_without_a_session_coupon(): void
    {
        $context = $this->context();
        $this->coupon();
        $payload = array_merge($this->payload($context['address']), [
            'coupon_code' => 'SAVE10',
            'discount_total' => 50,
            'total' => 1,
        ]);

        $this->actingAs($context['user'])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $context['user']->id,
            'discount_total' => 0,
            'total' => 51,
        ]);
        $this->assertDatabaseHas('payments', ['amount' => 51, 'gateway' => 'cod', 'status' => 'pending']);
    }

    #[DataProvider('invalidCheckoutCoupons')]
    public function test_checkout_revalidates_the_session_coupon_and_fails_clearly(array $attributes, string $message): void
    {
        $context = $this->context();
        $this->coupon($attributes);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->post(route('checkout.store'), $this->payload($context['address']))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error')
            ->assertSessionHas(CouponService::SESSION_KEY, 'SAVE10');

        $this->assertStringContainsString($message, session('error'));
        $this->assertDatabaseMissing('orders', ['user_id' => $context['user']->id]);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 5]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $context['cart']->id, 'quantity' => 2]);
    }

    public static function invalidCheckoutCoupons(): array
    {
        return [
            'expired' => [['expires_at' => '2000-01-01 00:00:00'], 'expired'],
            'inactive' => [['is_active' => false], 'inactive'],
            'below minimum order' => [['minimum_order' => 52], 'minimum order'],
            'invalid value' => [['value' => 101], 'invalid value'],
        ];
    }

    public function test_idempotent_replay_does_not_reapply_or_clear_a_new_session_coupon(): void
    {
        $context = $this->context();
        $this->coupon();
        $key = (string) Str::uuid();
        $payload = $this->payload($context['address'], $key);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.index'))
            ->assertSessionMissing(CouponService::SESSION_KEY);

        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 1,
        ]);
        $this->coupon(['code' => 'SAVE20', 'value' => 20]);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE20'])
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas(CouponService::SESSION_KEY, 'SAVE20');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('orders', ['discount_total' => 5.10, 'total' => 45.90]);
        $this->assertDatabaseHas('payments', ['amount' => 45.90, 'status' => 'pending']);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 3]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $context['cart']->id, 'quantity' => 1]);
    }

    public function test_order_endpoint_also_uses_and_clears_the_session_coupon(): void
    {
        $context = $this->context();
        $this->coupon(['type' => 'fixed', 'value' => 10]);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->post(route('orders.store'), $this->payload($context['address']))
            ->assertRedirect(route('orders.index'))
            ->assertSessionMissing(CouponService::SESSION_KEY);

        $this->assertDatabaseHas('orders', [
            'user_id' => $context['user']->id,
            'discount_total' => 10,
            'total' => 41,
        ]);
        $this->assertDatabaseHas('payments', ['amount' => 41, 'gateway' => 'cod', 'status' => 'pending']);
    }
}
