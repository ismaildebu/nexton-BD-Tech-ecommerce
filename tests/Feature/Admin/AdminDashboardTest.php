<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class AdminDashboardTest extends IdentityTestCase
{
    private function order(User $customer, string $number, float $total, string $status, string $paymentStatus): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'order_number' => $number,
            'total' => $total,
            'status' => $status,
            'payment_status' => $paymentStatus,
        ]);
    }

    public function test_dashboard_uses_only_paid_completed_revenue_and_non_admin_customers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        User::factory()->create();

        $this->order($customer, 'ORD-DASH-001', 250, 'completed', 'paid');
        $this->order($customer, 'ORD-DASH-002', 999, 'completed', 'unpaid');
        $this->order($customer, 'ORD-DASH-003', 999, 'processing', 'paid');
        $this->order($customer, 'ORD-DASH-004', 999, 'pending', 'unpaid');
        $this->order($customer, 'ORD-DASH-005', 999, 'cancelled', 'paid');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recognized revenue')
            ->assertSee('Non-admin customers');

        $this->assertSame(250.0, $response->viewData('revenue'));
        $this->assertSame(2, $response->viewData('customers'));
    }

    public function test_dashboard_has_real_stock_and_actionable_links_without_misleading_controls(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-DASH001',
            'business_name' => 'Dashboard Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Zero Stock Product',
            'slug' => 'zero-stock-product',
            'price' => 125,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Zero Stock Product')
            ->assertSee('0 units')
            ->assertSee('Manage products')
            ->assertSee(route('admin.categories.index'), false)
            ->assertSee(route('admin.brands.index'), false)
            ->assertSee(route('admin.wishlist.index'), false)
            ->assertDontSee('15 units')
            ->assertDontSee('Export CSV')
            ->assertDontSee('Add Product')
            ->assertDontSee('Projected Sales');
    }

    public function test_admin_navigation_drawer_has_accessible_focus_wiring(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());
        $drawer = $xpath->query('//*[@id="admin-navigation"]')->item(0);

        $this->assertNotNull($drawer);
        $this->assertSame('adminDrawer', $drawer->getAttribute('x-ref'));
        $this->assertSame('-1', $drawer->getAttribute('tabindex'));
        $response->assertSee('x-ref="adminDrawerOpen"', false)
            ->assertSee('aria-controls="admin-navigation"', false)
            ->assertSee('$refs.adminDrawer.focus()', false);
    }

    public function test_customer_directory_handles_phone_only_accounts_without_empty_email_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->phoneOnly()->create(['name' => 'Phone Only Customer']);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Phone Only Customer')
            ->assertDontSee('href="mailto:"', false);
    }

    public function test_admin_entry_redirects_and_is_guarded(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.dashboard'));
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_guests_are_sent_to_login_from_admin_entry(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customer_navigation_links_to_wishlist(): void
    {
        $this->actingAs(User::factory()->create())->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee(route('wishlist.index'), false);
    }
}
