<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class ProductStoreTest extends IdentityTestCase
{
    public function test_authenticated_user_can_create_product(): void
    {
        $user = User::factory()->create();
        Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-P002',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/products', ['name' => 'Test Product', 'slug' => 'test-product', 'price' => 100])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Test Product', 'slug' => 'test-product']);
    }
}
