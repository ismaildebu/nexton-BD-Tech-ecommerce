<?php

namespace Tests\Feature\Review;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class ReviewStoreTest extends IdentityTestCase
{
    public function test_user_can_submit_review(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-REV001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'P',
            'slug' => 'p-review',
            'price' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('reviews.store'), ['product_id' => $product->id, 'rating' => 5, 'comment' => 'Good'])
            ->assertRedirect(route('reviews.index'));

        $this->assertDatabaseHas('reviews', ['product_id' => $product->id]);
    }
}
