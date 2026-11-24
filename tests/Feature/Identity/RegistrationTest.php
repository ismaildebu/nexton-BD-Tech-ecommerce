<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Support\NullPhoneVerificationGateway;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RegistrationTest extends IdentityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Log::spy();
    }

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
            ->assertRedirect(route('verification.notice'));

        $this->assertGuest();

        $user = User::where('email', 'rahim@example.com')->firstOrFail();

        $this->assertSame($user->id, session('verification_user_id'));
        $this->assertNull($user->phone);
        $this->assertSame(UserStatus::PendingVerification, $user->status);
        $this->assertFalse($user->is_protected);
        $this->assertTrue(Hash::check('Str0ngPassword', $user->password));
        $this->assertNotSame('Str0ngPassword', $user->password);
        $this->assertNotNull($user->customerProfile);
        $this->assertNull($user->last_login_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_register_with_phone_only(): void
    {
        $this->post(route('register.store'), $this->payload(['email' => null, 'phone' => '01712345678']))
            ->assertRedirect(route('verification.notice'));

        $this->assertGuest();

        $user = User::where('phone', '+8801712345678')->firstOrFail();

        $this->assertSame($user->id, session('verification_user_id'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => null,
            'phone' => '+8801712345678',
            'phone_verified_at' => null,
            'status' => UserStatus::PendingVerification->value,
        ]);
    }

    public function test_registration_with_both_contacts_sends_both_challenges(): void
    {
        $this->post(route('register.store'), $this->payload(['phone' => '01712345678']))
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'rahim@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
        Log::shouldHaveReceived('info')->once()->with('Phone verification code', \Mockery::on(
            fn (array $context): bool => $context['phone'] === '+8801712345678'
                && $context['purpose'] === \App\Modules\Identity\Contracts\PhoneVerificationGateway::PURPOSE_VERIFICATION
        ));
    }

    public function test_registration_reports_a_missing_phone_provider_without_losing_the_account(): void
    {
        $this->app->instance(PhoneVerificationGateway::class, new NullPhoneVerificationGateway);

        $this->post(route('register.store'), $this->payload(['email' => null, 'phone' => '01712345678']))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => null,
            'phone' => '+8801712345678',
            'status' => UserStatus::PendingVerification->value,
        ]);
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
