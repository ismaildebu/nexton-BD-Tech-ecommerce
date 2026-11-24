<?php

namespace Tests\Feature\Cart;

use App\Models\Address;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Services\CouponService;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Identity\IdentityTestCase;

class CartStoreTest extends IdentityTestCase
{
    private function context(int $stock = 5): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-CART-'.uniqid(),
            'business_name' => 'Cart Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Cart Product',
            'slug' => 'cart-product-'.uniqid(),
            'price' => 10,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $inventory = Inventory::create(['product_id' => $product->id, 'quantity' => $stock]);
        $cart = Cart::create(['user_id' => $user->id]);

        return compact('user', 'product', 'inventory', 'cart');
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

    public function test_user_can_add_to_cart_and_repeated_adds_increment_quantity(): void
    {
        $context = $this->context();
        $payload = ['product_id' => $context['product']->id, 'quantity' => 2];

        $this->actingAs($context['user'])->post(route('cart.store'), $payload)->assertRedirect(route('cart.index'));
        $this->actingAs($context['user'])->post(route('cart.store'), ['product_id' => $context['product']->id, 'quantity' => 3])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 5,
        ]);
    }

    public function test_unapproved_product_cannot_be_added(): void
    {
        $context = $this->context();
        $context['product']->update(['status' => 'pending']);

        $this->actingAs($context['user'])
            ->post(route('cart.store'), ['product_id' => $context['product']->id, 'quantity' => 1])
            ->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $context['product']->id]);
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $context = $this->context();
        $context['product']->update(['is_active' => false]);

        $this->actingAs($context['user'])
            ->post(route('cart.store'), ['product_id' => $context['product']->id, 'quantity' => 1])
            ->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $context['product']->id]);
    }

    public function test_product_from_a_suspended_vendor_cannot_be_added(): void
    {
        $context = $this->context();
        $context['product']->vendor->update([
            'status' => 'suspended',
            'verification_status' => 'suspended',
        ]);

        $this->actingAs($context['user'])
            ->post(route('cart.store'), ['product_id' => $context['product']->id, 'quantity' => 1])
            ->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $context['product']->id]);
    }

    public function test_add_rejects_insufficient_stock_without_partial_writes(): void
    {
        $context = $this->context(2);

        $this->actingAs($context['user'])
            ->post(route('cart.store'), ['product_id' => $context['product']->id, 'quantity' => 3])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $context['product']->id]);
        $this->assertDatabaseHas('inventories', ['id' => $context['inventory']->id, 'quantity' => 2]);
    }

    public function test_cart_disables_checkout_when_the_vendor_becomes_unavailable(): void
    {
        $context = $this->context();
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 1,
        ]);
        Address::factory()->for($context['user'])->default()->create();
        $context['product']->vendor->update([
            'status' => 'suspended',
            'verification_status' => 'suspended',
        ]);

        $response = $this->actingAs($context['user'])->get(route('cart.index'))
            ->assertOk()
            ->assertSee('This product is no longer available.')
            ->assertSee('Update or remove unavailable items before checkout.');
        $xpath = $this->htmlXPath($response->getContent());
        $checkoutButton = $xpath->query('//form[@action="'.route('checkout.store').'"]//button[@type="submit"]')->item(0);
        $quantityForms = $xpath->query('//form[@action="'.route('cart.update', $item).'"][.//input[@name="quantity"]]');
        $decreaseButton = $quantityForms->item(0)->getElementsByTagName('button')->item(0);
        $increaseButton = $quantityForms->item(1)->getElementsByTagName('button')->item(0);

        $this->assertNotSame('', $checkoutButton->getAttribute('disabled'));
        $this->assertCount(2, $quantityForms);
        $this->assertNotSame('', $decreaseButton->getAttribute('disabled'));
        $this->assertNotSame('', $increaseButton->getAttribute('disabled'));
    }

    public function test_cart_caps_decrease_and_disables_increase_above_the_server_quantity_limit(): void
    {
        $context = $this->context(1500);
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 1000,
        ]);
        Address::factory()->for($context['user'])->default()->create();

        $response = $this->actingAs($context['user'])->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Update or remove unavailable items before checkout.');
        $xpath = $this->htmlXPath($response->getContent());
        $checkoutButton = $xpath->query('//form[@action="'.route('checkout.store').'"]//button[@type="submit"]')->item(0);
        $quantityForms = $xpath->query('//form[@action="'.route('cart.update', $item).'"][.//input[@name="quantity"]]');
        $decreaseForm = $quantityForms->item(0);
        $decreaseQuantity = $xpath->query('.//input[@name="quantity"]', $decreaseForm)->item(0);
        $decreaseButton = $decreaseForm->getElementsByTagName('button')->item(0);
        $increaseButton = $quantityForms->item(1)->getElementsByTagName('button')->item(0);

        $this->assertNotSame('', $checkoutButton->getAttribute('disabled'));
        $this->assertSame('999', $decreaseQuantity->getAttribute('value'));
        $this->assertSame('', $decreaseButton->getAttribute('disabled'));
        $this->assertNotSame('', $increaseButton->getAttribute('disabled'));
    }

    public function test_update_sets_exact_quantity_and_remove_deletes_the_item(): void
    {
        $context = $this->context();
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);

        $this->actingAs($context['user'])
            ->patch(route('cart.update', $item), ['quantity' => 4])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);

        $this->actingAs($context['user'])
            ->delete(route('cart.destroy', $item))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_update_rejects_quantity_above_stock(): void
    {
        $context = $this->context(3);
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);

        $this->actingAs($context['user'])
            ->patch(route('cart.update', $item), ['quantity' => 4])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 2]);
    }

    public function test_another_user_cannot_update_or_remove_cart_items(): void
    {
        $context = $this->context();
        $otherUser = User::factory()->create();
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);

        $this->actingAs($otherUser)->patch(route('cart.update', $item), ['quantity' => 3])->assertForbidden();
        $this->actingAs($otherUser)->delete(route('cart.destroy', $item))->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 2]);
    }

    public function test_cart_page_renders_update_and_remove_controls(): void
    {
        $context = $this->context(2);
        $item = CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);

        $this->actingAs($context['user'])->get(route('cart.index'))
            ->assertOk()
            ->assertSee('action="'.route('cart.update', $item).'"', false)
            ->assertSee('action="'.route('cart.destroy', $item).'"', false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('action="'.route('cart.coupon.apply').'"', false)
            ->assertDontSee('Coupon SAVE10 applied!');
    }

    public function test_applying_a_percentage_coupon_updates_cart_totals(): void
    {
        $context = $this->context();
        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);
        $this->coupon();

        $this->actingAs($context['user'])
            ->post(route('cart.coupon.apply'), ['code' => 'save10'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas(CouponService::SESSION_KEY, 'SAVE10');

        $this->actingAs($context['user'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Coupon SAVE10 applied.')
            ->assertSee('Discount (SAVE10)')
            ->assertSee('20.00')
            ->assertSee('2.00')
            ->assertSee('18.00')
            ->assertSee('action="'.route('cart.coupon.remove').'"', false);
    }

    public function test_applying_a_fixed_coupon_updates_cart_totals(): void
    {
        $context = $this->context();
        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);
        $this->coupon(['type' => 'fixed', 'value' => 5]);

        $this->actingAs($context['user'])
            ->post(route('cart.coupon.apply'), ['code' => 'SAVE10'])
            ->assertSessionHas(CouponService::SESSION_KEY, 'SAVE10');

        $this->actingAs($context['user'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('5.00')
            ->assertSee('15.00');
    }

    public function test_customer_can_remove_an_applied_coupon(): void
    {
        $context = $this->context();
        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);

        $this->actingAs($context['user'])
            ->withSession([CouponService::SESSION_KEY => 'SAVE10'])
            ->delete(route('cart.coupon.remove'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionMissing(CouponService::SESSION_KEY);

        $this->actingAs($context['user'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('action="'.route('cart.coupon.apply').'"', false)
            ->assertSee('20.00');
    }

    #[DataProvider('invalidCartCoupons')]
    public function test_cart_rejects_invalid_coupons_with_visible_errors(array $attributes, string $message): void
    {
        $context = $this->context();
        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);
        $this->coupon($attributes);

        $this->actingAs($context['user'])
            ->post(route('cart.coupon.apply'), ['code' => 'SAVE10'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error')
            ->assertSessionMissing(CouponService::SESSION_KEY);

        $this->actingAs($context['user'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($message)
            ->assertSee('20.00');
    }

    public static function invalidCartCoupons(): array
    {
        return [
            'below minimum order' => [['minimum_order' => 20.01], 'minimum order'],
            'not started' => [['starts_at' => '2999-01-01 00:00:00'], 'not active yet'],
            'expired' => [['expires_at' => '2000-01-01 00:00:00'], 'expired'],
            'inactive' => [['is_active' => false], 'inactive'],
            'invalid percentage' => [['value' => 101], 'invalid value'],
        ];
    }

    public function test_cart_revalidates_a_coupon_that_expires_after_application(): void
    {
        $context = $this->context();
        CartItem::create([
            'cart_id' => $context['cart']->id,
            'product_id' => $context['product']->id,
            'quantity' => 2,
        ]);
        $coupon = $this->coupon();

        $this->actingAs($context['user'])
            ->post(route('cart.coupon.apply'), ['code' => 'SAVE10'])
            ->assertSessionHas(CouponService::SESSION_KEY, 'SAVE10');

        $coupon->update(['expires_at' => '2000-01-01 00:00:00']);

        $this->actingAs($context['user'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Coupon SAVE10 has expired.')
            ->assertSee('20.00')
            ->assertDontSee('18.00')
            ->assertSessionMissing(CouponService::SESSION_KEY);
    }
}
