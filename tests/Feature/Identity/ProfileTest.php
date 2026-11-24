<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProfileTest extends IdentityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Log::spy();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'), ['name' => 'X'])->assertRedirect(route('login'));
    }

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create())->get(route('profile.edit'))->assertOk();
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '01712345678',
            'preferred_language' => 'bn',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('+8801712345678', $user->phone);
        $this->assertNull($user->email_verified_at, 'Changing the email must clear its verification.');
        $this->assertNull($user->phone_verified_at);
        $this->assertSame(UserStatus::PendingVerification, $user->status);
        $this->assertSame('bn', $user->customerProfile->preferred_language);

        Notification::assertSentTo($user, VerifyEmail::class);
        Log::shouldHaveReceived('info')->once()->with('Phone verification code', \Mockery::on(
            fn (array $context): bool => $context['phone'] === '+8801712345678'
        ));

        $this->get(route('profile.edit'))->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->assertSame($user->id, session('verification_user_id'));
    }

    public function test_changing_email_keeps_account_active_when_phone_is_verified(): void
    {
        $user = User::factory()->phoneOnly('+8801712345678')->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
            'phone' => '01712345678',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->phone_verified_at);
        $this->assertAuthenticatedAs($user);

        Notification::assertSentTo($user, VerifyEmail::class);
        Log::shouldNotHaveReceived('info');
    }

    public function test_changing_phone_keeps_account_active_when_email_is_verified(): void
    {
        $user = User::factory()->create(['email' => 'keep@example.com']);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'keep@example.com',
            'phone' => '01712345678',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->phone_verified_at);
        $this->assertAuthenticatedAs($user);

        Notification::assertNothingSent();
        Log::shouldHaveReceived('info')->once()->with('Phone verification code', \Mockery::on(
            fn (array $context): bool => $context['phone'] === '+8801712345678'
        ));
    }

    public function test_profile_page_links_to_verification_for_an_unverified_contact(): void
    {
        $user = User::factory()->create([
            'email' => 'new@example.com',
            'email_verified_at' => null,
            'phone' => '+8801712345678',
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(route('verification.notice'))
            ->assertSee(__('Verify now'));
    }

    public function test_profile_update_cannot_target_another_user(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create(['name' => 'Victim', 'email' => 'victim@example.com']);

        $this->actingAs($attacker)->patch(route('profile.update'), [
            'id' => $victim->id,
            'user_id' => $victim->id,
            'name' => 'Hacked',
            'email' => $attacker->email,
            'status' => 'suspended',
            'is_protected' => 1,
        ])->assertRedirect(route('profile.edit'));

        $victim->refresh();
        $attacker->refresh();

        $this->assertSame('Victim', $victim->name);
        $this->assertSame('victim@example.com', $victim->email);
        $this->assertSame(UserStatus::Active, $victim->status);

        $this->assertSame('Hacked', $attacker->name);
        $this->assertSame(UserStatus::Active, $attacker->status);
        $this->assertFalse($attacker->is_protected);
    }

    public function test_policy_denies_updating_another_users_profile(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('update', $user));
        $this->assertTrue(Gate::forUser($user)->denies('update', $other));
        $this->assertTrue(Gate::forUser($user)->denies('view', $other));
    }

    public function test_profile_update_rejects_an_email_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Name',
            'email' => 'taken@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_profile_update_cannot_remove_both_email_and_phone(): void
    {
        $user = User::factory()->create(['email' => 'keep@example.com']);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Name',
            'email' => '',
            'phone' => '',
        ])->assertSessionHasErrors(['email', 'phone']);

        $this->assertSame('keep@example.com', $user->fresh()->email);
    }

    public function test_protected_system_accounts_cannot_be_deleted(): void
    {
        $protected = User::factory()->protectedAccount()->create();
        $regular = User::factory()->create();

        $this->assertTrue(Gate::forUser($protected)->denies('delete', $protected));
        $this->assertTrue(Gate::forUser($regular)->allows('delete', $regular));
        $this->assertTrue(Gate::forUser($regular)->denies('delete', $protected));
    }
}
