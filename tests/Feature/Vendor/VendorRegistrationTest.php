<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
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
}
