<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Models\Category;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class AdminTaxonomyTest extends IdentityTestCase
{
    public function test_admin_can_create_edit_and_toggle_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.categories.create'))->assertOk()->assertSee('Create category');

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Home Audio',
            'slug' => 'Home Audio!',
            'is_active' => 1,
        ])->assertRedirect(route('admin.categories.index'))->assertSessionHas('status');

        $category = Category::where('slug', 'home-audio')->firstOrFail();
        $this->assertTrue($category->is_active);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Premium Audio',
            'slug' => 'Premium Audio',
            'is_active' => 1,
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Premium Audio', $category->fresh()->name);
        $this->assertSame('premium-audio', $category->fresh()->slug);

        $this->actingAs($admin)->patch(route('admin.categories.toggle', $category))
            ->assertSessionHas('status');
        $this->assertFalse($category->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.categories.toggle', $category));
        $this->assertTrue($category->fresh()->is_active);
    }

    public function test_admin_can_create_edit_and_toggle_brands(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.brands.store'), [
            'name' => 'Sound Co', 'slug' => 'Sound Co', 'is_active' => 1,
        ])->assertRedirect(route('admin.brands.index'));

        $brand = Brand::where('slug', 'sound-co')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.brands.update', $brand), [
            'name' => 'Sound Company', 'slug' => 'sound-company', 'is_active' => 1,
        ])->assertRedirect(route('admin.brands.index'));
        $this->assertSame('Sound Company', $brand->fresh()->name);

        $this->actingAs($admin)->patch(route('admin.brands.toggle', $brand));
        $this->assertFalse($brand->fresh()->is_active);
        $this->actingAs($admin)->patch(route('admin.brands.toggle', $brand));
        $this->assertTrue($brand->fresh()->is_active);
    }

    public function test_taxonomy_changes_require_an_admin(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.categories.store'), [
            'name' => 'No', 'slug' => 'no', 'is_active' => 1,
        ])->assertForbidden();
        $this->actingAs($customer)->post(route('admin.brands.store'), [
            'name' => 'No', 'slug' => 'no', 'is_active' => 1,
        ])->assertForbidden();
    }

    public function test_products_cannot_select_inactive_categories_or_brands(): void
    {
        $owner = User::factory()->create();
        Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VND-TAX001',
            'business_name' => 'Taxonomy Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $category = Category::create(['name' => 'Old Category', 'slug' => 'old-category', 'is_active' => false]);
        $brand = Brand::create(['name' => 'Old Brand', 'slug' => 'old-brand', 'is_active' => false]);

        $this->actingAs($owner)->post(route('vendor.products.store'), [
            'name' => 'Invalid Product', 'price' => 100, 'category_id' => $category->id, 'brand_id' => $brand->id,
        ])->assertSessionHasErrors(['category_id', 'brand_id']);

        $this->assertDatabaseMissing('products', ['name' => 'Invalid Product']);
    }
}
