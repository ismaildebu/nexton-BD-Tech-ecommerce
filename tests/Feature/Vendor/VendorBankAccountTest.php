<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorBankAccount;
use App\Modules\Vendor\Models\VendorUser;
use Illuminate\Support\Facades\Crypt;
use Tests\Feature\Identity\IdentityTestCase;

class VendorBankAccountTest extends IdentityTestCase
{
    private function vendor(User $owner, string $suffix = ''): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VND-BANK-'.uniqid().$suffix,
            'business_name' => 'Bank Shop'.$suffix,
            'verification_status' => 'approved',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_store_an_encrypted_account_and_only_sees_a_mask(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->vendor($owner);

        $this->actingAs($owner)->post(route('vendor.bank-accounts.store', $vendor), [
            'account_holder_name' => 'Shop Owner',
            'bank_name' => 'Example Bank',
            'branch_name' => 'Dhaka',
            'account_number' => '1234567890',
            'routing_number' => '987654',
            'account_type' => 'current',
            'is_default' => '1',
        ])->assertRedirect(route('vendor.bank-accounts.index', $vendor));

        $account = VendorBankAccount::firstOrFail();
        $this->assertSame('1234567890', Crypt::decryptString($account->account_number_encrypted));
        $this->assertNotSame('1234567890', $account->account_number_encrypted);
        $this->assertSame('7890', $account->account_number_last_four);
        $this->assertSame('****7890', $account->masked_account_number);
        $this->assertTrue($account->is_default);
        $this->assertArrayNotHasKey('account_number_encrypted', $account->toArray());

        $this->actingAs($owner)->get(route('vendor.bank-accounts.index', $vendor))
            ->assertOk()->assertSee('****7890')->assertDontSee('1234567890');
    }

    public function test_bank_account_validation_errors_are_visible(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->vendor($owner);
        $url = route('vendor.bank-accounts.index', $vendor);

        $this->actingAs($owner)->from($url)->post(route('vendor.bank-accounts.store', $vendor), [
            'account_holder_name' => 'Owner',
            'bank_name' => 'Bank',
            'account_number' => '12',
        ])->assertSessionHasErrors('account_number');

        $this->get($url)->assertSee('The account number field must be at least 6 characters.');
        $this->assertDatabaseCount('vendor_bank_accounts', 0);
    }

    public function test_cross_vendor_bank_account_access_is_denied(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $vendor = $this->vendor($owner);

        $this->actingAs($intruder)->get(route('vendor.bank-accounts.index', $vendor))->assertForbidden();
        $this->actingAs($intruder)->post(route('vendor.bank-accounts.store', $vendor), [
            'account_holder_name' => 'Intruder', 'bank_name' => 'Bank', 'account_number' => '12345678',
        ])->assertForbidden();

        $this->assertDatabaseCount('vendor_bank_accounts', 0);
    }

    public function test_only_active_membership_grants_non_owner_bank_access(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create();
        $vendor = $this->vendor($owner);
        VendorUser::create([
            'vendor_id' => $vendor->id, 'user_id' => $staff->id,
            'staff_role' => 'manager', 'status' => 'invited',
        ]);

        $this->actingAs($staff)->get(route('vendor.bank-accounts.index', $vendor))->assertForbidden();

        $vendor->staff()->where('user_id', $staff->id)->update(['status' => 'active']);
        $this->actingAs($staff)->get(route('vendor.bank-accounts.index', $vendor))->assertOk();
    }
}
