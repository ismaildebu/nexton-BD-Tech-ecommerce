<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class AdminVendorApprovalTest extends IdentityTestCase
{
    public function test_admin_can_approve_vendor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VND-TEST001',
            'business_name' => 'Shop',
            'verification_status' => 'pending',
            'status' => 'inactive',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.vendors.approve', $vendor))
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'verification_status' => 'approved']);
        $this->assertDatabaseHas('vendor_status_histories', ['vendor_id' => $vendor->id, 'new_verification_status' => 'approved']);
    }
}
