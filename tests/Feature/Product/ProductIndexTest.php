<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class ProductIndexTest extends IdentityTestCase
{
    public function test_products_page_loads(): void
    {
        $this->get('/products')->assertOk();
    }

    public function test_inactive_products_not_listed(): void
    {
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-P001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        Product::create(['vendor_id' => $vendor->id, 'name' => 'Hidden', 'slug' => 'hidden', 'price' => 10, 'is_active' => false, 'status' => 'pending']);

        $response = $this->get('/products');
        $response->assertOk()->assertDontSee('Hidden');
    }
}
