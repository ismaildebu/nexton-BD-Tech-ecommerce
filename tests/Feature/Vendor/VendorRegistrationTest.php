<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class VendorRegistrationTest extends IdentityTestCase
{
    public function test_guest_cannot_register_vendor(): void
    {
        $this->get('/vendor/register')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_submit_vendor_application(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/vendor/register', ['business_name' => 'Acme Shop'])
            ->assertRedirect(route('vendor.registration.status'));

        $this->assertDatabaseHas('vendors', [
            'owner_user_id' => $user->id,
            'business_name' => 'Acme Shop',
            'verification_status' => 'pending',
        ]);
    }

    public function test_duplicate_pending_application_is_not_created(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/vendor/register', ['business_name' => 'Original Shop']);
        $this->actingAs($owner)->post('/vendor/register', ['business_name' => 'Duplicate Shop'])
            ->assertSessionHas('status', 'An application already exists for this account.');

        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseHas('vendors', ['owner_user_id' => $owner->id, 'business_name' => 'Original Shop']);
    }

    public function test_existing_draft_application_is_reused_and_submitted(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'draft');

        $this->actingAs($owner)->post('/vendor/register', [
            'business_name' => 'Completed Draft', 'business_email' => 'shop@example.com',
        ])->assertRedirect(route('vendor.registration.status'));

        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id, 'business_name' => 'Completed Draft', 'business_email' => 'shop@example.com',
            'verification_status' => 'pending', 'status' => 'inactive',
        ]);
        $this->assertNotNull($vendor->refresh()->submitted_at);
    }

    public function test_owner_can_update_existing_business_profile_fields(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'approved', 'active');

        $this->actingAs($owner)->put(route('vendor.settings.update', $vendor), [
            'business_name' => 'Updated Business', 'business_type' => 'company',
            'business_email' => 'business@example.com', 'business_phone' => '+8801700000000',
            'contact_person' => 'Jane Owner', 'business_address' => '123 Market Road',
            'district' => 'Dhaka', 'upazila' => 'Dhanmondi', 'postal_code' => '1205',
            'business_description' => 'A trusted retailer', 'owner_user_id' => User::factory()->create()->id,
            'verification_status' => 'rejected', 'status' => 'blocked',
        ])->assertRedirect(route('vendor.settings.edit', $vendor));

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id, 'owner_user_id' => $owner->id, 'business_name' => 'Updated Business',
            'business_type' => 'company', 'business_email' => 'business@example.com', 'business_phone' => '+8801700000000',
            'contact_person' => 'Jane Owner', 'business_address' => '123 Market Road', 'district' => 'Dhaka',
            'upazila' => 'Dhanmondi', 'postal_code' => '1205', 'business_description' => 'A trusted retailer',
            'verification_status' => 'approved', 'status' => 'active',
        ]);
    }

    public function test_profile_management_is_owner_only_and_validation_is_visible(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'approved', 'active');
        $vendor->staff()->create(['user_id' => $intruder->id, 'staff_role' => 'manager', 'status' => 'active']);
        $url = route('vendor.settings.edit', $vendor);

        $this->actingAs($intruder)->get($url)->assertForbidden();
        $this->actingAs($intruder)->put(route('vendor.settings.update', $vendor), ['business_name' => 'Taken'])->assertForbidden();

        $this->actingAs($owner)->from($url)->put(route('vendor.settings.update', $vendor), [
            'business_name' => 'Valid Name', 'business_email' => 'not-an-email',
        ])->assertSessionHasErrors('business_email');
        $this->get($url)->assertSee('The business email field must be a valid email address.');
    }

    private function makeVendor(User $owner, string $verification = 'pending', string $status = 'inactive'): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id, 'vendor_code' => 'VND-REG-'.uniqid(),
            'business_name' => 'Registration Shop', 'verification_status' => $verification, 'status' => $status,
        ]);
    }
}
