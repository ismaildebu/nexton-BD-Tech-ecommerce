<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class ProductCatalogWorkflowTest extends IdentityTestCase
{
    private int $sequence = 0;

    private function product(array $productAttributes = [], array $vendorAttributes = [], int $stock = 5): Product
    {
        $this->sequence++;
        $vendor = Vendor::create(array_merge([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-CATALOG-'.$this->sequence,
            'business_name' => 'Catalog Vendor '.$this->sequence,
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ], $vendorAttributes));
        $product = Product::create(array_merge([
            'vendor_id' => $vendor->id,
            'name' => 'Catalog Product '.$this->sequence,
            'slug' => 'catalog-product-'.$this->sequence,
            'price' => 1000,
            'status' => 'approved',
            'is_active' => true,
        ], $productAttributes));
        Inventory::create(['product_id' => $product->id, 'quantity' => $stock]);

        return $product;
    }

    public function test_zero_stock_is_shown_and_purchase_controls_are_disabled(): void
    {
        $product = $this->product(['name' => 'Zero Stock Product'], [], 0);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Zero Stock Product')
            ->assertSee('Out of Stock')
            ->assertSee('disabled', false)
            ->assertDontSee('1,200')
            ->assertDontSee('line-through');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Out of stock')
            ->assertSee('disabled', false)
            ->assertDontSee('Color Option')
            ->assertDontSee('line-through');
    }

    public function test_products_from_rejected_suspended_or_inactive_vendors_are_not_public(): void
    {
        $states = [
            ['verification_status' => 'rejected', 'status' => 'active'],
            ['verification_status' => 'suspended', 'status' => 'active'],
            ['verification_status' => 'approved', 'status' => 'suspended'],
            ['verification_status' => 'approved', 'status' => 'inactive'],
        ];
        $hiddenProducts = [];

        foreach ($states as $state) {
            $product = $this->product(['name' => 'Hidden Vendor Product '.count($hiddenProducts)], $state);
            $hiddenProducts[] = $product;

            $this->get(route('products.index'))->assertOk()->assertDontSee($product->name);
            $this->get(route('products.show', $product))->assertNotFound();
        }

        $visibleProduct = $this->product(['name' => 'Visible Approved Product']);
        $homepage = $this->get(route('home'))->assertOk()->assertSee($visibleProduct->name);
        foreach ($hiddenProducts as $product) {
            $homepage->assertDontSee($product->name);
        }
    }

    public function test_unapproved_product_is_hidden_even_when_active(): void
    {
        $product = $this->product(['name' => 'Pending Public Product', 'status' => 'pending']);

        $this->get(route('products.index'))->assertOk()->assertDontSee('Pending Public Product');
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_category_pills_link_to_real_catalog_filters(): void
    {
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
        $audio = Category::create(['name' => 'Audio', 'slug' => 'audio', 'is_active' => true]);
        $electronicsProduct = $this->product(['name' => 'Filtered Electronics', 'category_id' => $electronics->id]);
        $this->product(['name' => 'Filtered Audio', 'category_id' => $audio->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('products.index', ['category' => $electronics->id]).'"', false);

        $this->get(route('products.index', ['category' => $electronics->id]))
            ->assertOk()
            ->assertSee('Filtered Electronics')
            ->assertDontSee('Filtered Audio')
            ->assertSee('href="'.route('products.show', $electronicsProduct).'"', false);
    }

    public function test_homepage_hides_spotlight_and_fictional_fallback_when_no_product_exists(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Spotlight')
            ->assertDontSee('Cast Lights Minimal')
            ->assertDontSee('GlowGalore Store')
            ->assertDontSee('action="'.route('cart.store').'"', false);
    }

    public function test_product_hearts_post_to_the_authenticated_wishlist_route(): void
    {
        $product = $this->product(['name' => 'Heart Workflow Product']);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('action="'.route('wishlist.store').'"', false);
        $this->actingAs($user)->get(route('products.index'))
            ->assertOk()
            ->assertSee('action="'.route('wishlist.store').'"', false);
        $this->actingAs($user)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('action="'.route('wishlist.store').'"', false);
    }
}
