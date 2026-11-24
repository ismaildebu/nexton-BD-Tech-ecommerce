<?php

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class InventoryStoreTest extends IdentityTestCase
{
    public function test_admin_can_adjust_inventory_with_an_audit_movement(): void
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
                'low_stock_threshold' => 2,
                'note' => 'Received damaged carton',
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 5, 'low_stock_threshold' => 2]);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'type' => 'adjustment_in',
            'quantity' => 5,
            'note' => 'Received damaged carton',
        ]);

        $this->actingAs($admin)->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Record adjustment')
            ->assertSee('Received damaged carton');
    }

    public function test_inventory_shows_validation_and_stock_errors_without_recording_a_movement(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-INV002',
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'P',
            'slug' => 'p-inv-error',
            'price' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.store'), ['product_id' => $product->id, 'quantity' => 0])
            ->assertSessionHasErrors('quantity');

        $this->actingAs($admin)->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.store'), ['product_id' => $product->id, 'quantity' => -1])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_non_admin_cannot_adjust_inventory(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.inventory.store'), ['product_id' => 1, 'quantity' => 1])
            ->assertForbidden();
    }
}
