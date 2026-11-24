<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationService;
use Tests\Feature\Identity\IdentityTestCase;

class NotificationStoreTest extends IdentityTestCase
{
    public function test_user_can_create_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notifications.store'), ['type' => 'info', 'data' => ['msg' => 'hi']])
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseHas('notifications', ['type' => 'info', 'notifiable_id' => $user->id]);
    }

    public function test_owned_notification_displays_data_and_can_be_marked_read(): void
    {
        $user = User::factory()->create();
        $notification = app(NotificationService::class)->create('order_update', $user, ['message' => 'Your order has shipped.']);

        $this->actingAs($user)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Your order has shipped.')
            ->assertSee('notification-unread', false)
            ->assertSee('action="'.route('notifications.read', $notification).'"', false);

        $this->actingAs($user)->patch(route('notifications.read', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('status');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = app(NotificationService::class)->create('info', $owner, ['message' => 'Private message']);

        $this->actingAs($otherUser)->patch(route('notifications.read', $notification))->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_admin_form_and_targeted_notification_creation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['name' => 'Selected Customer']);

        $this->actingAs($admin)->get(route('admin.notifications.create'))
            ->assertOk()
            ->assertSee('Selected Customer')
            ->assertSee('action="'.route('admin.notifications.store').'"', false);

        $this->actingAs($admin)->post(route('admin.notifications.store'), [
            'user_id' => $customer->id,
            'type' => 'success',
            'data' => ['message' => 'Your account was updated.', 'url' => route('customer.dashboard')],
        ])->assertRedirect(route('admin.notifications.index'))->assertSessionHas('status');

        $notification = Notification::where('notifiable_id', $customer->id)->firstOrFail();
        $this->assertSame('success', $notification->type);
        $this->assertSame('Your account was updated.', $notification->data['message']);
        $this->assertSame(route('customer.dashboard'), $notification->data['url']);
        $this->assertSame(User::class, $notification->notifiable_type);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $admin->id]);
    }

    public function test_admin_notification_creation_validates_target_type_and_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.notifications.store'), [
            'user_id' => 999999,
            'type' => '',
            'data' => ['message' => ''],
        ])->assertSessionHasErrors(['user_id', 'type', 'data.message']);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_non_admin_cannot_send_targeted_notifications(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('admin.notifications.store'), [
                'user_id' => $customer->id,
                'type' => 'info',
                'data' => ['message' => 'Not allowed'],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('notifications', 0);
    }
}
