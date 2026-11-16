<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class PasswordResetTest extends IdentityTestCase
{
    private const NEW_PASSWORD = 'N3wStrongPass';

    public function test_forgot_password_page_is_displayed(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'rahim@example.com']);

        $this->post(route('password.email'), ['email' => 'rahim@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token]))->assertOk();

            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_reset_token_cannot_be_reused(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $payload = [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ];

            $this->post(route('password.update'), $payload)->assertSessionHasNoErrors();
            $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');

            return true;
        });
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

    public function test_unknown_email_gets_the_same_response_and_no_notification(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status', __('If an account with that email exists, a password reset link has been sent.'))
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
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
}
