<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Tests\Feature\Identity\IdentityTestCase;

class CustomerDashboardTest extends IdentityTestCase
{
    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/customer/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/customer/dashboard')->assertOk();
    }

    public function test_dashboard_does_not_expose_other_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Alice']);
        $other = User::factory()->create(['name' => 'Bob']);

        $response = $this->actingAs($user)->get('/customer/dashboard');
        $response->assertOk()->assertSee('Alice')->assertDontSee('Bob');
    }
}
