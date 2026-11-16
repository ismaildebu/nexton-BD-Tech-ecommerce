<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;

class RegistrationTest extends IdentityTestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'phone' => null,
            'password' => 'Str0ngPassword',
            'password_confirmation' => 'Str0ngPassword',
        ], $overrides);
    }

    public function test_registration_page_is_displayed(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_user_can_register_with_email(): void
    {
        $this->post(route('register.store'), $this->payload())
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticated();

        $user = User::where('email', 'rahim@example.com')->firstOrFail();

        $this->assertNull($user->phone);
        $this->assertSame(UserStatus::PendingVerification, $user->status);
        $this->assertFalse($user->is_protected);
        $this->assertTrue(Hash::check('Str0ngPassword', $user->password));
        $this->assertNotSame('Str0ngPassword', $user->password);
        $this->assertNotNull($user->customerProfile);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_user_can_register_with_phone_only(): void
    {
        $this->post(route('register.store'), $this->payload(['email' => null, 'phone' => '01712345678']))
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', ['email' => null, 'phone' => '+8801712345678']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'rahim@example.com']);

        $this->post(route('register.store'), $this->payload(['email' => 'RAHIM@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_phone(): void
    {
        User::factory()->phoneOnly('+8801712345678')->create();

        $this->post(route('register.store'), $this->payload(['email' => null, 'phone' => '017 1234 5678']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_rejects_missing_email_and_phone(): void
    {
        $this->post(route('register.store'), $this->payload(['email' => '', 'phone' => '']))
            ->assertSessionHasErrors(['email', 'phone']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_invalid_phone_number(): void
    {
        $this->post(route('register.store'), $this->payload(['email' => null, 'phone' => '12345']))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_validation_failure_creates_no_incomplete_records(): void
    {
        $this->post(route('register.store'), $this->payload([
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]))->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customer_profiles', 0);
        $this->assertGuest();
    }

    public function test_registration_cannot_set_status_or_protected_flag(): void
    {
        $this->post(route('register.store'), $this->payload([
            'status' => 'active',
            'is_protected' => 1,
            'email_verified_at' => now()->toDateTimeString(),
        ]));

        $user = User::where('email', 'rahim@example.com')->firstOrFail();

        $this->assertSame(UserStatus::PendingVerification, $user->status);
        $this->assertFalse($user->is_protected);
        $this->assertNull($user->email_verified_at);
    }

    public function test_database_rejects_a_user_without_email_and_phone(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => null, 'phone' => null]);
    }
}
