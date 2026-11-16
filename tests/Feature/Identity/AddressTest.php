<?php

namespace Tests\Feature\Identity;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\QueryException;

class AddressTest extends IdentityTestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'home',
            'full_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'address_line' => 'House 12, Road 3',
            'division' => 'Khulna',
            'district' => 'Jashore',
            'upazila' => 'Sadar',
            'postal_code' => '7400',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('addresses.index'))->assertRedirect(route('login'));
        $this->post(route('addresses.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_user_can_create_an_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'full_name' => 'Rahim Uddin',
            'phone' => '+8801712345678',
            'is_default' => true,
        ]);
    }

    public function test_the_first_address_becomes_the_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->payload(['is_default' => '0']));

        $this->assertTrue($user->addresses()->firstOrFail()->is_default);
    }

    public function test_address_ownership_cannot_be_supplied_by_the_client(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->payload(['user_id' => $other->id]));

        $this->assertSame(1, $user->addresses()->count());
        $this->assertSame(0, $other->addresses()->count());
    }

    public function test_address_validation_failure_creates_no_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->payload([
            'full_name' => '',
            'phone' => '12345',
            'postal_code' => 'abcd',
            'type' => 'castle',
        ]))->assertSessionHasErrors(['full_name', 'phone', 'postal_code', 'type']);

        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_index_lists_only_the_users_own_addresses(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Address::factory()->for($user)->create(['full_name' => 'Mine Visible']);
        Address::factory()->for($other)->create(['full_name' => 'Secret Neighbour']);

        $this->actingAs($user)->get(route('addresses.index'))
            ->assertOk()
            ->assertSee('Mine Visible')
            ->assertDontSee('Secret Neighbour');
    }

    public function test_user_can_update_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->default()->create();

        $this->actingAs($user)->get(route('addresses.edit', $address))->assertOk();

        $this->actingAs($user)->put(route('addresses.update', $address), $this->payload([
            'full_name' => 'Updated Name',
            'district' => 'Khulna',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $user->id,
            'full_name' => 'Updated Name',
            'district' => 'Khulna',
        ]);
    }

    public function test_user_can_delete_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->default()->create();

        $this->actingAs($user)->delete(route('addresses.destroy', $address))
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_access_another_users_address(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $address = Address::factory()->for($owner)->default()->create(['full_name' => 'Owner Name']);

        $this->actingAs($user)->get(route('addresses.edit', $address))->assertNotFound();

        $this->actingAs($user)->put(route('addresses.update', $address), $this->payload(['full_name' => 'Hacked']))
            ->assertNotFound();

        $this->actingAs($user)->patch(route('addresses.default', $address))->assertNotFound();

        $this->actingAs($user)->delete(route('addresses.destroy', $address))->assertNotFound();

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'full_name' => 'Owner Name']);
    }

    public function test_only_one_default_address_exists_per_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('addresses.store'), $this->payload(['full_name' => 'First', 'is_default' => '1']));
        $this->actingAs($user)->post(route('addresses.store'), $this->payload(['full_name' => 'Second', 'is_default' => '1']));
        $this->actingAs($user)->post(route('addresses.store'), $this->payload(['full_name' => 'Third', 'is_default' => '0']));

        $this->assertSame(3, $user->addresses()->count());
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
        $this->assertSame('Second', $user->addresses()->where('is_default', true)->firstOrFail()->full_name);
    }

    public function test_default_address_can_be_changed(): void
    {
        $user = User::factory()->create();
        $first = Address::factory()->for($user)->default()->create();
        $second = Address::factory()->for($user)->create();

        $this->actingAs($user)->patch(route('addresses.default', $second))
            ->assertRedirect(route('addresses.index'));

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    public function test_deleting_the_default_address_promotes_another_one(): void
    {
        $user = User::factory()->create();
        $older = Address::factory()->for($user)->create();
        $default = Address::factory()->for($user)->default()->create();

        $this->actingAs($user)->delete(route('addresses.destroy', $default));

        $this->assertTrue($older->fresh()->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    public function test_default_addresses_of_different_users_do_not_conflict(): void
    {
        Address::factory()->for(User::factory())->default()->create();
        Address::factory()->for(User::factory())->default()->create();

        $this->assertSame(2, Address::where('is_default', true)->count());
    }

    public function test_database_rejects_two_default_addresses_for_one_user(): void
    {
        $user = User::factory()->create();
        Address::factory()->for($user)->default()->create();

        $this->expectException(QueryException::class);

        Address::factory()->for($user)->default()->create();
    }
}
