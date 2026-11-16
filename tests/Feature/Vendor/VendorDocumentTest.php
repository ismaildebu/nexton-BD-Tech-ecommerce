<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Identity\IdentityTestCase;

class VendorDocumentTest extends IdentityTestCase
{
    public function test_vendor_can_upload_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $user->id,
            'vendor_code' => 'VND-DOC001',
            'business_name' => 'Shop',
            'verification_status' => 'pending',
            'status' => 'inactive',
            'submitted_at' => now(),
        ]);

        $file = UploadedFile::fake()->create('id.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('vendor.documents.store', $vendor), [
                'document_type' => 'national_id',
                'file' => $file,
            ])
            ->assertRedirect(route('vendor.documents.index', $vendor));

        $this->assertDatabaseHas('vendor_documents', ['vendor_id' => $vendor->id, 'document_type' => 'national_id']);
    }
}
