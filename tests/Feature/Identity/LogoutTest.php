<?php

namespace Tests\Feature\Identity;

use App\Models\User;

class LogoutTest extends IdentityTestCase
{
    public function test_logout_signs_the_user_out_and_invalidates_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['marker' => 'must-not-survive'])
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull(session('marker'));
    }

    public function test_guest_cannot_use_logout(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }
}
