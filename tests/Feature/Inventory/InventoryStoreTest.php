<?php

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class InventoryStoreTest extends IdentityTestCase
{
    public function test_admin_can_create_inventory(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-INV001',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'P',
            'slug' => 'p-inv',
            'price' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.inventory.store'), [
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 5]);
    }
}
