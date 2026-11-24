<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use Illuminate\Support\Facades\Storage;
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

    public function test_rejection_deactivates_products_clears_approval_and_shows_reason(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'approved', 'active');
        $vendor->forceFill(['approved_at' => now(), 'approved_by' => $admin->id])->save();
        $product = $this->makeProduct($vendor, true);

        $this->actingAs($admin)->post(route('admin.vendors.reject', $vendor), ['reason' => 'Documents could not be verified'])
            ->assertRedirect(route('admin.vendors.index'));

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id, 'verification_status' => 'rejected', 'status' => 'inactive',
            'approved_at' => null, 'approved_by' => null, 'rejection_reason' => 'Documents could not be verified',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
        $this->actingAs($admin)->get(route('admin.vendors.show', $vendor))->assertSee('Documents could not be verified');
        $this->actingAs($owner)->get(route('vendor.registration.status'))->assertSee('Documents could not be verified');
    }

    public function test_suspended_vendor_cannot_be_rejected_and_suspension_hides_products(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'approved', 'active');
        $product = $this->makeProduct($vendor, true);

        $this->actingAs($admin)->post(route('admin.vendors.suspend', $vendor), ['reason' => 'Policy review'])
            ->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'verification_status' => 'suspended', 'status' => 'suspended']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);

        $this->actingAs($admin)->post(route('admin.vendors.reject', $vendor), ['reason' => 'Conflicting rejection'])
            ->assertSessionHas('error');
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'verification_status' => 'suspended', 'status' => 'suspended', 'rejection_reason' => null]);
    }

    public function test_reason_validation_is_scoped_to_the_submitted_vendor_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->makeVendor(User::factory()->create(), 'approved', 'active');

        foreach (['reject' => 'reject_reason', 'suspend' => 'suspend_reason'] as $action => $invalidId) {
            $this->actingAs($admin)
                ->from(route('admin.vendors.show', $vendor))
                ->post(route('admin.vendors.'.$action, $vendor), [
                    'reason_action' => $action,
                    'reason' => '',
                ])
                ->assertSessionHasErrors('reason');

            $response = $this->actingAs($admin)->get(route('admin.vendors.show', $vendor))->assertOk();
            $xpath = $this->htmlXPath($response->getContent());
            $otherId = $action === 'reject' ? 'suspend_reason' : 'reject_reason';

            $this->assertSame('true', $xpath->query('//*[@id="'.$invalidId.'"]')->item(0)->getAttribute('aria-invalid'));
            $this->assertSame('', $xpath->query('//*[@id="'.$otherId.'"]')->item(0)->getAttribute('aria-invalid'));
            $this->assertSame('', $xpath->query('//*[@id="reject_reason"]')->item(0)->getAttribute('value'));
            $this->assertSame('', $xpath->query('//*[@id="suspend_reason"]')->item(0)->getAttribute('value'));
        }
    }

    public function test_approval_clears_stale_rejection_metadata(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $vendor->forceFill(['rejected_at' => now(), 'rejected_by' => $admin->id, 'rejection_reason' => 'Stale'])->save();

        $this->actingAs($admin)->post(route('admin.vendors.approve', $vendor))->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id, 'verification_status' => 'approved', 'status' => 'active',
            'rejected_at' => null, 'rejected_by' => null, 'rejection_reason' => null,
        ]);
    }

    public function test_rejected_vendor_can_correct_and_resubmit(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner, 'rejected', 'inactive');
        $vendor->forceFill(['rejected_at' => now(), 'rejection_reason' => 'Fix the address'])->save();

        $this->actingAs($owner)->get(route('vendor.register.create'))
            ->assertOk()->assertSee('Fix the address');
        $this->actingAs($owner)->post(route('vendor.register.store'), [
            'business_name' => 'Corrected Shop', 'business_address' => 'New address',
        ])->assertRedirect(route('vendor.registration.status'));

        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id, 'business_name' => 'Corrected Shop', 'business_address' => 'New address',
            'verification_status' => 'pending', 'status' => 'inactive', 'rejection_reason' => null,
        ]);
    }

    public function test_admin_document_preview_is_inline_and_vendor_scoped(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $vendor = $this->makeVendor($owner);
        $other = $this->makeVendor(User::factory()->create());
        Storage::disk('local')->put('vendor-documents/id.pdf', '%PDF-1.4 test');
        $document = VendorDocument::create([
            'vendor_id' => $vendor->id, 'document_type' => 'national_id', 'original_filename' => 'id.pdf',
            'storage_disk' => 'local', 'storage_path' => 'vendor-documents/id.pdf',
            'mime_type' => 'application/pdf', 'file_size' => 14,
        ]);
        $foreignDocument = VendorDocument::create([
            'vendor_id' => $other->id, 'document_type' => 'national_id', 'original_filename' => 'other.pdf',
            'storage_disk' => 'local', 'storage_path' => 'vendor-documents/id.pdf',
            'mime_type' => 'application/pdf', 'file_size' => 14,
        ]);

        $this->actingAs($admin)->get(route('admin.vendors.documents.preview', [$vendor, $document]))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="id.pdf"')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($owner)->get(route('admin.vendors.documents.preview', [$vendor, $document]))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.vendors.documents.preview', [$vendor, $foreignDocument]))->assertNotFound();
    }

    private function makeVendor(User $owner, string $verification = 'pending', string $status = 'inactive'): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id, 'vendor_code' => 'VND-ADMIN-'.uniqid(),
            'business_name' => 'Admin Review Shop', 'verification_status' => $verification,
            'status' => $status, 'submitted_at' => now(),
        ]);
    }

    private function makeProduct(Vendor $vendor, bool $active = false): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id, 'name' => 'Reviewed Product', 'slug' => 'reviewed-'.uniqid(),
            'price' => '100.00', 'status' => $active ? 'approved' : 'pending', 'is_active' => $active,
        ]);
    }
}
