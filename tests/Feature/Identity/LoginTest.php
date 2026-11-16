<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\Request;

class LoginTest extends IdentityTestCase
{
    public function test_login_page_is_displayed(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create(['email' => 'rahim@example.com']);

        $this->post(route('login.store'), ['login' => 'Rahim@Example.com', 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_can_login_with_phone(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();

        $this->post(route('login.store'), ['login' => '01712345678', 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_pending_verification_user_can_login(): void
    {
        $user = User::factory()->create(['status' => UserStatus::PendingVerification]);

        $this->post(route('login.store'), ['login' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected_with_the_same_message(): void
    {
        $user = User::factory()->create();

        $wrongPassword = $this->post(route('login.store'), ['login' => $user->email, 'password' => 'wrong-password']);
        $wrongPassword->assertSessionHasErrors('login');
        $this->assertGuest();

        $unknownUser = $this->post(route('login.store'), ['login' => 'nobody@example.com', 'password' => 'wrong-password']);
        $unknownUser->assertSessionHasErrors('login');

        $this->assertSame(
            session('errors')->first('login'),
            __('These credentials do not match our records.')
        );
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->suspended()->create();

        $this->post(route('login.store'), ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post(route('login.store'), ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_successful_login_regenerates_the_session(): void
    {
        $user = User::factory()->create();

        $session = $this->app['session.store'];
        $session->start();
        $oldSessionId = $session->getId();

        $request = Request::create('/login', 'POST');
        $request->setLaravelSession($session);

        $this->app->make(AuthenticationService::class)->login($request, $user);

        $this->assertNotSame($oldSessionId, $session->getId());
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['login' => $user->email, 'password' => 'wrong-password']);
        }

        // Even the correct password is refused while the limiter is active.
        $this->post(route('login.store'), ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('profile.edit'));
    }
}
