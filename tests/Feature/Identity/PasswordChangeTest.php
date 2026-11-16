<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordChangeTest extends IdentityTestCase
{
    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'password' => 'N3wStrongPass1',
            'password_confirmation' => 'N3wStrongPass1',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_change_rejects_a_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'not-my-password',
            'password' => 'N3wStrongPass1',
            'password_confirmation' => 'N3wStrongPass1',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_can_be_changed_with_the_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'N3wStrongPass1',
            'password_confirmation' => 'N3wStrongPass1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('N3wStrongPass1', $user->fresh()->password));
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
