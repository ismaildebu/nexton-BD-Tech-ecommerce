<?php

namespace Tests\Feature\Subscription;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class SubscriptionStoreTest extends IdentityTestCase
{
    private function vendor(User $owner, string $code): Vendor
    {
        return Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => $code,
            'business_name' => 'Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function subscription(
        Vendor $vendor,
        string $status = 'inactive',
        ?string $startsAt = null,
        ?string $expiresAt = null,
    ): Subscription {
        return Subscription::create([
            'vendor_id' => $vendor->id,
            'plan' => 'basic',
            'status' => $status,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);
    }

    public function test_vendor_owner_can_create_inactive_subscription_with_dates(): void
    {
        $user = User::factory()->create();
        $vendor = $this->vendor($user, 'VND-SUB001');
        $startsAt = now()->addDay()->startOfSecond();
        $expiresAt = now()->addMonth()->startOfSecond();

        $this->actingAs($user)
            ->post(route('subscriptions.store'), [
                'vendor_id' => $vendor->id,
                'plan' => 'basic',
                'status' => 'active',
                'starts_at' => $startsAt->toDateTimeString(),
                'expires_at' => $expiresAt->toDateTimeString(),
            ])
            ->assertRedirect(route('subscriptions.index'));

        $this->assertDatabaseHas('subscriptions', [
            'vendor_id' => $vendor->id,
            'plan' => 'basic',
            'status' => 'inactive',
            'starts_at' => $startsAt->toDateTimeString(),
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }

    public function test_user_cannot_create_subscription_for_another_vendor(): void
    {
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB002');

        $this->actingAs(User::factory()->create())
            ->post(route('subscriptions.store'), ['vendor_id' => $vendor->id, 'plan' => 'basic'])
            ->assertForbidden();

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_admin_can_create_an_inactive_subscription_with_an_expiry_only(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB003');
        $expiresAt = now()->addMonth()->startOfSecond();

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.store'), [
                'vendor_id' => $vendor->id,
                'plan' => 'premium',
                'expires_at' => $expiresAt->toDateTimeString(),
            ])
            ->assertRedirect(route('admin.subscriptions.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subscriptions', [
            'vendor_id' => $vendor->id,
            'plan' => 'premium',
            'status' => 'inactive',
            'starts_at' => null,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }

    public function test_admin_subscription_creation_shows_vendor_validation_errors(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.store'), ['vendor_id' => 999999, 'plan' => 'premium'])
            ->assertSessionHasErrors('vendor_id');

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_admin_can_activate_a_subscription_with_explicit_dates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB004');
        $subscription = $this->subscription($vendor);
        $startsAt = now()->addHour()->startOfSecond();
        $expiresAt = now()->addMonth()->startOfSecond();

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.transition', $subscription), [
                'status' => 'active',
                'starts_at' => $startsAt->toDateTimeString(),
                'expires_at' => $expiresAt->toDateTimeString(),
            ])
            ->assertRedirect(route('admin.subscriptions.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'active',
            'starts_at' => $startsAt->toDateTimeString(),
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }

    public function test_activation_defaults_a_missing_start_date_to_now(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB005');
        $subscription = $this->subscription($vendor);
        $before = now();

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.transition', $subscription), ['status' => 'active'])
            ->assertSessionHas('status');

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertTrue($subscription->starts_at->between($before->copy()->subSeconds(5), now()->addSeconds(5)));
        $this->assertNull($subscription->expires_at);
    }

    public function test_activation_rejects_an_invalid_or_past_expiry_without_mutation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB006');
        $subscription = $this->subscription($vendor);
        $startsAt = now()->addDay()->startOfSecond();

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.transition', $subscription), [
                'status' => 'active',
                'starts_at' => $startsAt->toDateTimeString(),
                'expires_at' => now()->addHour()->startOfSecond()->toDateTimeString(),
            ])
            ->assertSessionHasErrors('expires_at');

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'inactive']);

        $pastStart = now()->subMonth()->startOfSecond()->toDateTimeString();
        $pastExpiry = now()->subDay()->startOfSecond()->toDateTimeString();
        $expired = $this->subscription($vendor, 'inactive', $pastStart, $pastExpiry);

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.transition', $expired), [
                'status' => 'active',
                'subscription_id' => $expired->id,
                'starts_at' => $pastStart,
                'expires_at' => $pastExpiry,
            ])
            ->assertSessionHas('error')
            ->assertSessionHasInput('starts_at', $pastStart)
            ->assertSessionHasInput('expires_at', $pastExpiry);

        $this->assertDatabaseHas('subscriptions', ['id' => $expired->id, 'status' => 'inactive']);
    }

    public function test_subscription_validation_restores_dates_only_on_the_submitted_row(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB-ROW-INPUT');
        $submitted = $this->subscription($vendor);
        $other = $this->subscription($vendor);
        $startsAt = now()->addDays(2)->startOfSecond()->format('Y-m-d\TH:i');
        $expiresAt = now()->addDay()->startOfSecond()->format('Y-m-d\TH:i');

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.transition', $submitted), [
                'status' => 'active',
                'subscription_id' => $submitted->id,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
            ])
            ->assertSessionHasErrors('expires_at');

        $response = $this->actingAs($admin)->get(route('admin.subscriptions.index'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());
        $submittedExpiry = $xpath->query('//*[@id="subscription-expiry-'.$submitted->id.'"]')->item(0);
        $otherExpiry = $xpath->query('//*[@id="subscription-expiry-'.$other->id.'"]')->item(0);

        $this->assertSame($startsAt, $xpath->query('//*[@id="subscription-start-'.$submitted->id.'"]')->item(0)->getAttribute('value'));
        $this->assertSame($expiresAt, $submittedExpiry->getAttribute('value'));
        $this->assertSame('', $xpath->query('//*[@id="subscription-start-'.$other->id.'"]')->item(0)->getAttribute('value'));
        $this->assertSame('', $otherExpiry->getAttribute('value'));
        $this->assertSame('true', $submittedExpiry->getAttribute('aria-invalid'));
        $this->assertSame('', $otherExpiry->getAttribute('aria-invalid'));
    }

    public function test_cancellation_ends_a_started_subscription_at_the_current_time(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB007');
        $startsAt = now()->subDay()->startOfSecond()->toDateTimeString();
        $subscription = $this->subscription($vendor, 'active', $startsAt);
        $before = now();

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.transition', $subscription), ['status' => 'cancelled'])
            ->assertSessionHas('status');

        $subscription->refresh();
        $this->assertSame('cancelled', $subscription->status);
        $this->assertNotNull($subscription->expires_at);
        $this->assertTrue($subscription->expires_at->between($before->copy()->subSeconds(5), now()->addSeconds(5)));
        $this->assertSame($startsAt, $subscription->starts_at->toDateTimeString());
    }

    public function test_cancellation_does_not_extend_an_expired_or_not_yet_started_subscription(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB008');
        $pastExpiry = now()->subDay()->startOfSecond()->toDateTimeString();
        $expired = $this->subscription(
            $vendor,
            'active',
            now()->subMonth()->startOfSecond()->toDateTimeString(),
            $pastExpiry,
        );
        $future = $this->subscription($vendor, 'active', now()->addDay()->startOfSecond()->toDateTimeString());

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.transition', $expired), ['status' => 'cancelled'])
            ->assertSessionHas('status');
        $this->actingAs($admin)
            ->post(route('admin.subscriptions.transition', $future), ['status' => 'cancelled'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $expired->id,
            'status' => 'cancelled',
            'expires_at' => $pastExpiry,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $future->id,
            'status' => 'cancelled',
            'expires_at' => null,
        ]);
    }

    public function test_invalid_subscription_transitions_are_rejected_without_mutation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB009');

        foreach ([
            ['inactive', 'cancelled'],
            ['active', 'active'],
            ['cancelled', 'active'],
            ['cancelled', 'cancelled'],
        ] as [$current, $target]) {
            $subscription = $this->subscription($vendor, $current);

            $this->actingAs($admin)
                ->from(route('admin.subscriptions.index'))
                ->post(route('admin.subscriptions.transition', $subscription), ['status' => $target])
                ->assertRedirect(route('admin.subscriptions.index'))
                ->assertSessionHas('error');

            $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => $current]);
        }
    }

    public function test_non_admin_cannot_transition_a_subscription(): void
    {
        $owner = User::factory()->create();
        $vendor = $this->vendor($owner, 'VND-SUB010');
        $subscription = $this->subscription($vendor);

        $this->actingAs($owner)
            ->post(route('admin.subscriptions.transition', $subscription), ['status' => 'active'])
            ->assertForbidden();

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'inactive']);
    }

    public function test_subscription_creation_rejects_an_expiry_before_its_start(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB011');

        $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.store'), [
                'vendor_id' => $vendor->id,
                'starts_at' => now()->addDays(2)->startOfSecond()->toDateTimeString(),
                'expires_at' => now()->addDay()->startOfSecond()->toDateTimeString(),
            ])
            ->assertSessionHasErrors('expires_at');

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_admin_subscription_page_exposes_valid_workflow_actions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = $this->vendor(User::factory()->create(), 'VND-SUB012');
        $this->subscription($vendor, 'inactive');
        $this->subscription($vendor, 'active');
        $this->subscription($vendor, 'cancelled');

        $this->actingAs($admin)
            ->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertSee('Create subscription')
            ->assertSee('Activate')
            ->assertSee('Cancel')
            ->assertSee('Cancelled');
    }
}
