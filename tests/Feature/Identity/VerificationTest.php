<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Contracts\PhoneVerificationGateway;
use App\Modules\Identity\Enums\UserStatus;
use Closure;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use LogicException;
use Mockery;

class VerificationTest extends IdentityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Log::spy();
    }

    public function test_verification_notice_requires_a_verification_session(): void
    {
        $this->get(route('verification.notice'))->assertRedirect(route('login'));
    }

    public function test_verification_notice_displays_pending_contacts(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');

        $this->withSession(['verification_user_id' => $user->id])
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee($user->phone)
            ->assertSee(route('verification.phone'));
    }

    public function test_verification_notice_activates_a_pending_user_with_a_verified_contact(): void
    {
        $user = User::factory()->create(['status' => UserStatus::PendingVerification]);

        $this->withSession(['verification_user_id' => $user->id])
            ->get(route('verification.notice'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertSame(UserStatus::Active, $user->fresh()->status);
        $this->assertNull(session('verification_user_id'));
    }

    public function test_email_can_be_resent(): void
    {
        $user = $this->pendingEmailUser();

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.resend'), ['channel' => 'email'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_signed_email_link_verifies_and_activates_a_pending_account(): void
    {
        $user = $this->pendingEmailUser();

        $this->withSession(['verification_user_id' => $user->id])
            ->get($this->emailVerificationUrl($user))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(session('verification_user_id'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame(UserStatus::Active, $user->fresh()->status);

        $this->post(route('login.store'), ['login' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_signed_link_with_the_wrong_email_hash_is_rejected(): void
    {
        $user = $this->pendingEmailUser();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('another@example.com'),
        ]);

        $this->get($url)->assertRedirect(route('login'))->assertSessionHas('error');

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(UserStatus::PendingVerification, $user->fresh()->status);
    }

    public function test_tampered_email_link_is_rejected(): void
    {
        $user = $this->pendingEmailUser();
        $url = str_replace(sha1($user->email), str_repeat('0', 40), $this->emailVerificationUrl($user));

        $this->get($url)->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_email_link_is_rejected(): void
    {
        $user = $this->pendingEmailUser();
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_phone_code_verifies_and_activates_a_pending_account(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->withSession(['verification_user_id' => $user->id])
                ->post(route('verification.resend'), ['channel' => 'phone'])
                ->assertSessionHas('status'),
        );

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(session('verification_user_id'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertSame(UserStatus::Active, $user->fresh()->status);

        $this->post(route('login.store'), ['login' => $user->phone, 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_phone_code_does_not_verify_the_account(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->app->make(PhoneVerificationGateway::class)
                ->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION),
        );
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
        $this->assertSame(UserStatus::PendingVerification, $user->fresh()->status);
    }

    public function test_expired_phone_code_is_rejected(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->app->make(PhoneVerificationGateway::class)
                ->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION),
        );

        $this->travel(11)->minutes();

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_phone_resend_cooldown_preserves_the_original_code(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->withSession(['verification_user_id' => $user->id])
                ->post(route('verification.resend'), ['channel' => 'phone'])
                ->assertSessionHas('status'),
        );

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.resend'), ['channel' => 'phone'])
            ->assertSessionHasErrors('code');

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertSame(UserStatus::Active, $user->fresh()->status);
    }

    public function test_maximum_failed_attempts_consume_the_phone_challenge(): void
    {
        config(['services.phone_verification.max_attempts' => 2]);

        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->app->make(PhoneVerificationGateway::class)
                ->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION),
        );
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');
        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');
        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);
    }

    public function test_phone_code_is_single_use(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $code = $this->capturePhoneCode(
            fn () => $this->app->make(PhoneVerificationGateway::class)
                ->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION),
        );

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasNoErrors();

        $this->withSession(['verification_user_id' => $user->id])
            ->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_pending_authenticated_session_is_downgraded_to_verification_only(): void
    {
        $user = $this->pendingEmailUser();

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertRedirect(route('verification.notice'));

        $this->assertGuest();
        $this->assertSame($user->id, session('verification_user_id'));
    }

    public function test_active_user_can_verify_a_new_phone_without_losing_the_session(): void
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'phone' => '+8801712345678',
            'phone_verified_at' => null,
        ]);
        $code = $this->capturePhoneCode(
            fn () => $this->actingAs($user)
                ->post(route('verification.resend'), ['channel' => 'phone'])
                ->assertSessionHas('status'),
        );

        $this->actingAs($user)->post(route('verification.phone.store'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_active_user_can_verify_a_new_email_without_losing_the_session(): void
    {
        $user = User::factory()->create([
            'email' => 'new@example.com',
            'email_verified_at' => null,
            'phone' => '+8801712345678',
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user)->get($this->emailVerificationUrl($user))
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_log_phone_driver_is_disabled_in_production(): void
    {
        $user = $this->pendingPhoneUser('+8801712345678');
        $this->app->instance('env', 'production');

        $this->expectException(LogicException::class);

        $this->app->make(PhoneVerificationGateway::class)
            ->sendCode($user, PhoneVerificationGateway::PURPOSE_VERIFICATION);
    }

    private function pendingEmailUser(): User
    {
        return User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'status' => UserStatus::PendingVerification,
        ]);
    }

    private function pendingPhoneUser(string $phone): User
    {
        $user = User::factory()->phoneOnly($phone)->create();
        $user->forceFill([
            'status' => UserStatus::PendingVerification,
            'phone_verified_at' => null,
        ])->save();

        return $user->refresh();
    }

    private function emailVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
    }

    private function capturePhoneCode(Closure $send): string
    {
        Log::spy();
        $code = null;

        $send();

        Log::shouldHaveReceived('info')->once()->with('Phone verification code', Mockery::on(
            function (array $context) use (&$code): bool {
                if (($context['purpose'] ?? null) !== PhoneVerificationGateway::PURPOSE_VERIFICATION) {
                    return false;
                }

                $code = $context['code'];

                return true;
            }
        ));

        $this->assertIsString($code);

        return $code;
    }
}
