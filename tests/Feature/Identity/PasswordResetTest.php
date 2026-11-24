<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;

class PasswordResetTest extends IdentityTestCase
{
    private const NEW_PASSWORD = 'N3wStrongPass';

    private const GENERIC_MESSAGE = 'If an account with that email or mobile number exists, password reset instructions have been sent.';

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Log::spy();
    }

    public function test_forgot_password_page_is_displayed(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee(__('Email or mobile number'));
    }

    public function test_phone_password_reset_page_is_displayed(): void
    {
        $this->get(route('password.phone'))->assertOk();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'rahim@example.com']);
        $token = $this->emailResetToken($user);
        $oldRememberToken = $user->remember_token;

        config(['session.driver' => 'database']);
        $this->addUserSessions($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertNotSame($oldRememberToken, $user->fresh()->remember_token);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_reset_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $token = $this->emailResetToken($user);

        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ];

        $this->post(route('password.update'), $payload)->assertSessionHasNoErrors();
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_invalid_token_does_not_change_the_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_unknown_email_and_phone_get_the_same_response(): void
    {
        $this->post(route('password.email'), ['login' => 'nobody@example.com'])
            ->assertSessionHas('status', self::GENERIC_MESSAGE)
            ->assertSessionHasNoErrors();

        $this->post(route('password.email'), ['login' => '01799999999'])
            ->assertSessionHas('status', self::GENERIC_MESSAGE)
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
        Log::shouldNotHaveReceived('info');
    }

    public function test_pending_accounts_get_no_reset_instructions(): void
    {
        $emailUser = User::factory()->unverified()->create(['status' => UserStatus::PendingVerification]);
        $phoneUser = User::factory()->phoneOnly('+8801712345678')->create([
            'status' => UserStatus::PendingVerification,
            'phone_verified_at' => null,
        ]);

        $this->post(route('password.email'), ['login' => $emailUser->email])
            ->assertSessionHas('status', self::GENERIC_MESSAGE);
        $this->post(route('password.email'), ['login' => $phoneUser->phone])
            ->assertSessionHas('status', self::GENERIC_MESSAGE);

        Notification::assertNothingSent();
        Log::shouldNotHaveReceived('info');
    }

    public function test_pending_account_with_a_verified_contact_can_reset_password(): void
    {
        $user = User::factory()->create([
            'email' => 'pending.verified@example.com',
            'status' => UserStatus::PendingVerification,
        ]);
        $token = $this->emailResetToken($user);

        $this->assertSame(UserStatus::Active, $user->fresh()->status);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_phone_reset_cooldown_does_not_reveal_an_existing_account(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();

        $this->post(route('password.email'), ['login' => $user->phone])
            ->assertSessionHas('status', self::GENERIC_MESSAGE)
            ->assertSessionHasNoErrors();
        $this->post(route('password.email'), ['login' => $user->phone])
            ->assertSessionHas('status', self::GENERIC_MESSAGE)
            ->assertSessionHasNoErrors();
    }

    public function test_phone_reset_invalidates_an_outstanding_email_token(): void
    {
        $user = User::factory()->create([
            'phone' => '+8801712345678',
            'phone_verified_at' => now(),
        ]);
        $emailToken = $this->emailResetToken($user);
        $phoneCode = $this->phoneResetCode($user);

        $this->post(route('password.phone.update'), [
            'phone' => $user->phone,
            'code' => $phoneCode,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->post(route('password.update'), [
            'token' => $emailToken,
            'email' => $user->email,
            'password' => 'AnotherStrongPass1',
            'password_confirmation' => 'AnotherStrongPass1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_email_reset_invalidates_an_outstanding_phone_code(): void
    {
        $user = User::factory()->create([
            'phone' => '+8801712345678',
            'phone_verified_at' => now(),
        ]);
        $emailToken = $this->emailResetToken($user);
        $phoneCode = $this->phoneResetCode($user);

        $this->post(route('password.update'), [
            'token' => $emailToken,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->post(route('password.phone.update'), [
            'phone' => $user->phone,
            'code' => $phoneCode,
            'password' => 'AnotherStrongPass1',
            'password_confirmation' => 'AnotherStrongPass1',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'any-token',
            'email' => $user->email,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_phone_password_can_be_reset_and_code_is_single_use(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();
        $oldRememberToken = $user->remember_token;
        $code = $this->phoneResetCode($user);

        config(['session.driver' => 'database']);
        $this->addUserSessions($user);

        $payload = [
            'phone' => $user->phone,
            'code' => $code,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ];

        $this->post(route('password.phone.update'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertNotSame($oldRememberToken, $user->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);

        $this->post(route('password.phone.update'), $payload)->assertSessionHasErrors('code');
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_password_reset_code_cannot_be_used_for_account_verification(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();
        $code = $this->phoneResetCode($user);

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->post(route('password.phone.update'), [
            'phone' => $user->phone,
            'code' => $code,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));
    }

    public function test_email_reset_token_cannot_be_used_after_account_is_suspended(): void
    {
        $user = User::factory()->create();
        $token = $this->emailResetToken($user);

        $user->forceFill(['status' => UserStatus::Suspended])->save();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_phone_reset_code_cannot_be_used_after_account_is_suspended(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();
        $code = $this->phoneResetCode($user);

        $user->forceFill(['status' => UserStatus::Suspended])->save();

        $this->post(route('password.phone.update'), [
            'phone' => $user->phone,
            'code' => $code,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    private function emailResetToken(User $user): string
    {
        $this->post(route('password.email'), ['login' => $user->email])
            ->assertSessionHas('status', self::GENERIC_MESSAGE);

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->assertIsString($token);

        return $token;
    }

    private function phoneResetCode(User $user): string
    {
        return $this->capturePhoneCode(
            fn () => $this->post(route('password.email'), ['login' => $user->phone])
                ->assertSessionHas('status', self::GENERIC_MESSAGE),
            PhoneVerificationGateway::PURPOSE_PASSWORD_RESET,
        );
    }

    private function capturePhoneCode(Closure $send, string $purpose): string
    {
        Log::spy();
        $code = null;

        $send();

        Log::shouldHaveReceived('info')->once()->with('Phone verification code', Mockery::on(
            function (array $context) use (&$code, $purpose): bool {
                if (($context['purpose'] ?? null) !== $purpose) {
                    return false;
                }

                $code = $context['code'];

                return true;
            }
        ));

        $this->assertIsString($code);

        return $code;
    }

    private function addUserSessions(User $user): void
    {
        DB::table('sessions')->insert([
            [
                'id' => 'first-session',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'first-browser',
                'payload' => 'first',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'second-session',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'second-browser',
                'payload' => 'second',
                'last_activity' => now()->timestamp,
            ],
        ]);
    }
}
