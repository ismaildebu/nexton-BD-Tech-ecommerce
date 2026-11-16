<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Tests\Feature\Identity\IdentityTestCase;

class NotificationStoreTest extends IdentityTestCase
{
    public function test_user_can_create_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notifications.store'), ['type' => 'info', 'data' => ['msg' => 'hi']])
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseHas('notifications', ['type' => 'info']);
    }
}
