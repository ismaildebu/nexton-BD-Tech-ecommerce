<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Identity\IdentityTestCase;

class DemoDataSeederTest extends IdentityTestCase
{
    public function test_demo_seeder_is_idempotent_and_creates_usable_accounts(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('vendors', 1);
        $this->assertDatabaseCount('products', 10);
        $this->assertDatabaseCount('inventories', 10);
        $this->assertDatabaseCount('orders', 10);
        $this->assertDatabaseCount('order_items', 10);
        $this->assertDatabaseCount('payments', 10);
        $this->assertDatabaseCount('reviews', 10);
        $this->assertDatabaseCount('coupons', 1);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseMissing('order_items', ['vendor_id' => null]);
        $this->assertDatabaseMissing('payments', ['vendor_id' => null]);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertSame(UserStatus::Active, $admin->status);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('password', $admin->password));

        $this->assertDatabaseHas('users', [
            'email' => 'vendor@example.com',
            'status' => 'active',
            'is_admin' => false,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
            'status' => 'active',
            'is_admin' => false,
        ]);
    }
}
