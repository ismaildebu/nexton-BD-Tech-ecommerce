<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => 1])->save();

        return $user;
    }

    private function vendor(VendorVerificationStatus $verification = VendorVerificationStatus::Approved): Vendor
    {
        static $n = 0;
        $n++;
        $owner = User::factory()->create();

        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VND-ADM-'.$n.'-'.uniqid(),
            'business_name' => 'Admin Test Business '.$n,
            'business_type' => 'individual',
            'business_email' => 'adm'.$n.'-'.uniqid().'@example.com',
            'business_phone' => '+88018000'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'contact_person' => 'Test Person',
            'business_address' => 'Test Address',
            'district' => 'Dhaka',
            'upazila' => 'Dhanmondi',
            'postal_code' => '1205',
            'verification_status' => $verification,
            'status' => VendorStatus::Active,
        ]);
    }

    private function product(Vendor $vendor, string $status = 'pending'): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Item '.uniqid(),
            'slug' => 'item-'.uniqid(),
            'price' => '150.00',
            'status' => $status,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_approve_pending_product_and_it_goes_live(): void
    {
        $product = $this->product($this->vendor());

        $this->actingAs($this->admin())
            ->post(route('admin.products.approve', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'approved', 'is_active' => true]);
        $this->get(route('products.show', $product))->assertOk();
    }

    public function test_admin_can_reject_pending_product(): void
    {
        $product = $this->product($this->vendor());

        $this->actingAs($this->admin())
            ->post(route('admin.products.reject', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'rejected', 'is_active' => false]);
    }

    public function test_non_admin_cannot_approve_or_reject(): void
    {
        $vendor = $this->vendor();
        $product = $this->product($vendor);

        $this->actingAs($vendor->owner)->post(route('admin.products.approve', $product))->assertForbidden();
        $this->actingAs($vendor->owner)->post(route('admin.products.reject', $product))->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending', 'is_active' => false]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $product = $this->product($this->vendor());

        $this->post(route('admin.products.approve', $product))->assertRedirect(route('login'));
    }

    public function test_rejected_product_cannot_be_approved_directly(): void
    {
        $product = $this->product($this->vendor(), 'rejected');

        $this->actingAs($this->admin())
            ->post(route('admin.products.approve', $product))
            ->assertSessionHas('product_error');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'rejected', 'is_active' => false]);
    }

    public function test_product_of_unapproved_vendor_cannot_be_approved(): void
    {
        $product = $this->product($this->vendor(VendorVerificationStatus::Pending));

        $this->actingAs($this->admin())
            ->post(route('admin.products.approve', $product))
            ->assertSessionHas('product_error');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending', 'is_active' => false]);
    }

    public function test_admin_list_can_filter_by_status(): void
    {
        $vendor = $this->vendor();
        $pending = $this->product($vendor, 'pending');
        $approved = $this->product($vendor, 'approved');

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->name)
            ->assertDontSee($approved->name);
    }
}