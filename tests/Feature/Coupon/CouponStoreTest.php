<?php

namespace Tests\Feature\Coupon;

use App\Models\User;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Services\CouponService;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Identity\IdentityTestCase;

class CouponStoreTest extends IdentityTestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => 1,
        ], $overrides);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ], $overrides));
    }

    public function test_admin_can_create_coupon(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload([
                'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ]))
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'SAVE10')->firstOrFail();
        $this->assertSame('percent', $coupon->type);
        $this->assertSame('10.00', $coupon->value);
        $this->assertSame('0.00', $coupon->minimum_order);
        $this->assertTrue($coupon->is_active);
        $this->assertNotNull($coupon->starts_at);
        $this->assertNotNull($coupon->expires_at);
    }

    public function test_admin_coupon_page_renders_the_create_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('action="'.route('admin.coupons.store').'"', false)
            ->assertSee('name="code"', false)
            ->assertSee('name="type"', false)
            ->assertSee('name="value"', false)
            ->assertSee('name="minimum_order"', false)
            ->assertSee('name="starts_at"', false)
            ->assertSee('name="expires_at"', false)
            ->assertSee('name="is_active"', false);
    }

    public function test_percentage_and_fixed_values_have_conditional_validation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload(['code' => 'OVER100', 'value' => 101]))
            ->assertSessionHasErrors('value');

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload([
                'code' => 'FIX150',
                'type' => 'fixed',
                'value' => 150,
            ]))
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', ['code' => 'FIX150', 'type' => 'fixed', 'value' => 150]);
        $this->assertDatabaseMissing('coupons', ['code' => 'OVER100']);
    }

    public function test_discount_value_and_minimum_order_must_be_valid(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload(['value' => 0]))
            ->assertSessionHasErrors('value');

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload(['code' => 'MIN', 'minimum_order' => -1]))
            ->assertSessionHasErrors('minimum_order');
    }

    public function test_invalid_schedule_is_visible_on_the_admin_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->from(route('admin.coupons.index'))
            ->post(route('admin.coupons.store'), $this->payload([
                'starts_at' => '2026-12-01T10:00',
                'expires_at' => '2026-12-01T09:00',
            ]))
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHasErrors('expires_at');

        $message = session('errors')->first('expires_at');

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee($message);
    }

    public function test_admin_can_create_an_inactive_coupon(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), $this->payload(['is_active' => 0]))
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', ['code' => 'SAVE10', 'is_active' => false]);
    }

    public function test_coupon_routes_keep_admin_middleware(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.coupons.index'))->assertForbidden();
        $this->actingAs($customer)
            ->post(route('admin.coupons.store'), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseMissing('coupons', ['code' => 'SAVE10']);
    }

    #[DataProvider('validDiscounts')]
    public function test_valid_percentage_and_fixed_coupons_calculate_discounts(string $type, string $value, float $expected): void
    {
        $coupon = $this->coupon(['type' => $type, 'value' => $value]);
        $service = app(CouponService::class);

        $applicable = $service->findApplicable(' save10 ', 200);

        $this->assertTrue($coupon->is($applicable));
        $this->assertSame($expected, $service->discount($applicable, 200));
    }

    public static function validDiscounts(): array
    {
        return [
            'percentage' => ['percent', '10', 20.0],
            'fixed amount' => ['fixed', '25.50', 25.5],
            'fixed amount capped at subtotal' => ['fixed', '250', 200.0],
        ];
    }

    #[DataProvider('invalidCoupons')]
    public function test_invalid_coupons_are_rejected(array $attributes, string $message): void
    {
        $this->coupon($attributes);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($message);

        app(CouponService::class)->findApplicable('SAVE10', 200);
    }

    public static function invalidCoupons(): array
    {
        return [
            'below minimum order' => [['minimum_order' => 200.01], 'minimum order'],
            'not started' => [['starts_at' => '2999-01-01 00:00:00'], 'not active yet'],
            'expired' => [['expires_at' => '2000-01-01 00:00:00'], 'expired'],
            'inactive' => [['is_active' => false], 'inactive'],
            'percentage over one hundred' => [['value' => 101], 'invalid value'],
            'zero value' => [['value' => 0], 'invalid value'],
            'negative fixed value' => [['type' => 'fixed', 'value' => -5], 'invalid value'],
        ];
    }

    public function test_unknown_coupon_code_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Coupon MISSING is invalid.');

        app(CouponService::class)->findApplicable('missing', 200);
    }
}
