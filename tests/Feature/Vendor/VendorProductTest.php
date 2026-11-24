<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductTest extends TestCase
{
    use RefreshDatabase;

       private function vendorFor(User $user, VendorVerificationStatus $verification = VendorVerificationStatus::Approved): Vendor
    {
        static $sequence = 0;
        $sequence++;

        return Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-TEST-'.$sequence.'-'.uniqid(),
            'business_name' => 'Test Business '.$sequence,
            'business_type' => 'individual',
            'business_email' => 'vendor'.$sequence.'-'.uniqid().'@example.com',
            'business_phone' => '+88017000'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
            'contact_person' => 'Test Person',
            'business_address' => 'Test Address',
            'district' => 'Dhaka',
            'upazila' => 'Dhanmondi',
            'postal_code' => '1205',
            'verification_status' => $verification,
            'status' => VendorStatus::Active,
        ]);
    }

    private function productFor(Vendor $vendor, string $name = 'Sample', bool $active = false): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'price' => '100.00',
            'status' => $active ? 'approved' : 'pending',
            'is_active' => $active,
        ]);
    }

    public function test_approved_vendor_can_create_pending_inactive_product(): void
    {
        $user = User::factory()->create();
        $vendor = $this->vendorFor($user);

        $this->actingAs($user)
            ->post(route('vendor.products.store'), ['name' => 'Lamp', 'price' => '250.50'])
            ->assertRedirect(route('vendor.products.index'));

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id, 'name' => 'Lamp', 'price' => '250.50',
            'status' => 'pending', 'is_active' => false,
        ]);
    }

    public function test_vendor_cannot_override_vendor_id_status_or_active_flag(): void
    {
        $user = User::factory()->create();
        $vendor = $this->vendorFor($user);
        $other = $this->vendorFor(User::factory()->create());

        $this->actingAs($user)->post(route('vendor.products.store'), [
            'name' => 'Hack', 'price' => '10', 'vendor_id' => $other->id, 'status' => 'approved', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Hack', 'vendor_id' => $vendor->id, 'status' => 'pending', 'is_active' => false]);
    }

    public function test_unapproved_vendor_cannot_create_product(): void
    {
        $user = User::factory()->create();
        $this->vendorFor($user, VendorVerificationStatus::Pending);

        $this->actingAs($user)
            ->post(route('vendor.products.store'), ['name' => 'Nope', 'price' => '10'])
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['name' => 'Nope']);
    }

    public function test_vendor_cannot_edit_or_update_another_vendors_product(): void
    {
        $user = User::factory()->create();
        $this->vendorFor($user);
        $product = $this->productFor($this->vendorFor(User::factory()->create()), 'Theirs');

        $this->actingAs($user)->get(route('vendor.products.edit', $product))->assertForbidden();
        $this->actingAs($user)->put(route('vendor.products.update', $product), ['name' => 'Taken', 'price' => '1'])->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Theirs']);
    }

    public function test_vendor_can_update_own_product(): void
    {
        $user = User::factory()->create();
        $vendor = $this->vendorFor($user);
        $product = $this->productFor($vendor, 'Old');

        $this->actingAs($user)
            ->put(route('vendor.products.update', $product), ['name' => 'New', 'price' => '99.99'])
            ->assertRedirect(route('vendor.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New', 'price' => '99.99', 'vendor_id' => $vendor->id]);
    }

    public function test_index_lists_only_own_products(): void
    {
        $user = User::factory()->create();
        $this->productFor($this->vendorFor($user), 'MineProduct');
        $this->productFor($this->vendorFor(User::factory()->create()), 'OthersProduct');

        $this->actingAs($user)->get(route('vendor.products.index'))
            ->assertOk()->assertSee('MineProduct')->assertDontSee('OthersProduct');
    }

    public function test_inactive_product_is_hidden_from_public_show(): void
    {
        $product = $this->productFor($this->vendorFor(User::factory()->create()), 'Hidden');

        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_editing_an_approved_product_requires_reapproval_before_it_is_live(): void
    {
        $this->withoutVite();
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendorFor($owner);
        $product = $this->productFor($vendor, 'Approved Product', true);

        $this->actingAs($owner)->put(route('vendor.products.update', $product), [
            'name' => 'Edited Product', 'price' => '199.00',
        ])->assertRedirect(route('vendor.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id, 'name' => 'Edited Product', 'status' => 'pending', 'is_active' => false,
        ]);
        $this->get(route('products.show', $product))->assertNotFound();
        $this->get(route('products.index'))->assertDontSee('Edited Product');

        $this->actingAs($admin)->post(route('admin.products.approve', $product))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'approved', 'is_active' => true]);
        $this->get(route('products.show', $product))->assertOk()->assertSee('Edited Product');
    }

    public function test_vendor_can_deactivate_a_live_product(): void
    {
        $owner = User::factory()->create();
        $product = $this->productFor($this->vendorFor($owner), 'Live Product', true);

        $this->actingAs($owner)->delete(route('vendor.products.destroy', $product))
            ->assertRedirect(route('vendor.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
        $this->get(route('products.show', $product))->assertNotFound();
    }
}