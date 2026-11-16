<?php

namespace Tests\Feature\Wishlist;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class WishlistStoreTest extends IdentityTestCase
{
    public function test_user_can_add_to_wishlist(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-W001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'P',
            'slug' => 'p-wish',
            'price' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('wishlist.store'), ['product_id' => $product->id])
            ->assertRedirect(route('wishlist.index'));

        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }
}
