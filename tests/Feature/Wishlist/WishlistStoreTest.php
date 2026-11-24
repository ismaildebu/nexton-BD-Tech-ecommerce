<?php

namespace Tests\Feature\Wishlist;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Wishlist\Models\Wishlist;
use Tests\Feature\Identity\IdentityTestCase;

class WishlistStoreTest extends IdentityTestCase
{
    private function context(int $stock = 5): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-W001',
            'business_name' => 'Wishlist Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Wishlist Product',
            'slug' => 'p-wish',
            'price' => 10,
            'status' => 'approved',
            'is_active' => true,
        ]);
        Inventory::create(['product_id' => $product->id, 'quantity' => $stock]);

        return compact('user', 'product');
    }

    public function test_user_can_add_to_wishlist(): void
    {
        $context = $this->context();

        $this->actingAs($context['user'])
            ->post(route('wishlist.store'), ['product_id' => $context['product']->id])
            ->assertRedirect(route('wishlist.index'));

        $this->assertDatabaseHas('wishlists', ['user_id' => $context['user']->id, 'product_id' => $context['product']->id]);
    }

    public function test_guest_is_redirected_to_login_when_adding_to_wishlist(): void
    {
        $context = $this->context();

        $this->post(route('wishlist.store'), ['product_id' => $context['product']->id])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_user_can_remove_own_wishlist_item(): void
    {
        $context = $this->context();
        $item = Wishlist::create(['user_id' => $context['user']->id, 'product_id' => $context['product']->id]);

        $this->actingAs($context['user'])
            ->delete(route('wishlist.destroy', $item))
            ->assertRedirect(route('wishlist.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('wishlists', ['id' => $item->id]);
    }

    public function test_user_cannot_remove_another_users_wishlist_item(): void
    {
        $context = $this->context();
        $otherUser = User::factory()->create();
        $item = Wishlist::create(['user_id' => $otherUser->id, 'product_id' => $context['product']->id]);

        $this->actingAs($context['user'])->delete(route('wishlist.destroy', $item))->assertForbidden();

        $this->assertDatabaseHas('wishlists', ['id' => $item->id]);
    }

    public function test_wishlist_rows_link_to_product_and_expose_remove_and_cart_actions(): void
    {
        $context = $this->context();
        $item = Wishlist::create(['user_id' => $context['user']->id, 'product_id' => $context['product']->id]);
        Wishlist::create(['user_id' => User::factory()->create()->id, 'product_id' => $context['product']->id]);

        $this->actingAs($context['user'])->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('href="'.route('products.show', $context['product']).'"', false)
            ->assertSee('action="'.route('wishlist.destroy', $item).'"', false)
            ->assertSee('action="'.route('cart.store').'"', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_out_of_stock_wishlist_item_cannot_be_added_to_cart_from_the_list(): void
    {
        $context = $this->context(0);
        Wishlist::create(['user_id' => $context['user']->id, 'product_id' => $context['product']->id]);

        $this->actingAs($context['user'])->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Currently unavailable')
            ->assertDontSee('action="'.route('cart.store').'"', false);
    }
}
