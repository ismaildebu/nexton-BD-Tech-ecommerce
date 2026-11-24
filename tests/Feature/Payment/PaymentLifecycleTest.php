<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Str;
use Tests\Feature\Identity\IdentityTestCase;

class PaymentLifecycleTest extends IdentityTestCase
{
    private function context(string $paymentStatus = 'pending', string $orderStatus = 'pending'): array
    {
        $user = User::factory()->create();
        $orderPaymentStatus = match ($paymentStatus) {
            'paid' => 'paid',
            'refunded' => 'refunded',
            default => 'unpaid',
        };
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PAY-'.Str::upper(Str::random(8)),
            'total' => 100,
            'status' => $orderStatus,
            'payment_status' => $orderPaymentStatus,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => 100,
            'gateway' => 'cod',
            'status' => $paymentStatus,
        ]);

        return compact('order', 'payment');
    }

    public function test_admin_can_mark_a_pending_payment_paid_and_sync_the_order(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.payments.transition', $context['payment']), [
                'status' => 'paid',
                'transaction_id' => 'COD-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $context['payment']->id,
            'status' => 'paid',
            'transaction_id' => 'COD-123',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'paid']);
    }

    public function test_admin_can_refund_a_paid_payment_and_sync_the_order(): void
    {
        $context = $this->context('paid');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.payments.transition', $context['payment']), ['status' => 'refunded'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'refunded']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'refunded']);
    }

    public function test_order_payment_status_requires_the_full_aggregate_paid_amount(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);
        $context['payment']->update(['amount' => 60]);
        $second = Payment::create([
            'order_id' => $context['order']->id,
            'amount' => 40,
            'gateway' => 'cod',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.payments.transition', $context['payment']), ['status' => 'paid'])
            ->assertSessionHas('status');
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'partially_paid']);

        $this->actingAs($admin)->post(route('admin.payments.transition', $second), ['status' => 'paid'])
            ->assertSessionHas('status');
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'paid']);
    }

    public function test_refunding_one_payment_does_not_mark_the_whole_order_refunded(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);
        $context['payment']->update(['amount' => 60]);
        $second = Payment::create([
            'order_id' => $context['order']->id,
            'amount' => 40,
            'gateway' => 'cod',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.payments.transition', $context['payment']), ['status' => 'paid']);
        $this->actingAs($admin)->post(route('admin.payments.transition', $second), ['status' => 'paid']);
        $this->actingAs($admin)->post(route('admin.payments.transition', $context['payment']), ['status' => 'refunded'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', ['id' => $second->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'partially_refunded']);
    }

    public function test_paid_payment_transition_cannot_be_repeated(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.payments.transition', $context['payment']), [
            'status' => 'paid',
            'transaction_id' => 'COD-FIRST',
        ])->assertSessionHas('status');

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->post(route('admin.payments.transition', $context['payment']), [
                'status' => 'paid',
                'transaction_id' => 'COD-SECOND',
            ])
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'id' => $context['payment']->id,
            'status' => 'paid',
            'transaction_id' => 'COD-FIRST',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => 'paid']);
    }

    public function test_invalid_payment_transitions_do_not_mutate_records(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach ([
            ['pending', 'refunded'],
            ['paid', 'paid'],
            ['refunded', 'paid'],
            ['refunded', 'refunded'],
        ] as [$current, $target]) {
            $context = $this->context($current);
            $orderPaymentStatus = $current === 'pending' ? 'unpaid' : $current;

            $this->actingAs($admin)
                ->post(route('admin.payments.transition', $context['payment']), ['status' => $target])
                ->assertSessionHas('error');

            $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => $current]);
            $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'payment_status' => $orderPaymentStatus]);
        }
    }

    public function test_cancelled_order_cannot_be_marked_paid(): void
    {
        $context = $this->context('pending', 'cancelled');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.payments.transition', $context['payment']), ['status' => 'paid'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $context['order']->id, 'status' => 'cancelled', 'payment_status' => 'unpaid']);
    }

    public function test_payment_validation_restores_transaction_input_only_on_the_submitted_row(): void
    {
        $first = $this->context();
        $second = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);
        $transactionId = str_repeat('X', 129);

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->post(route('admin.payments.transition', $second['payment']), [
                'status' => 'paid',
                'payment_id' => $second['payment']->id,
                'transaction_id' => $transactionId,
            ])
            ->assertSessionHasErrors('transaction_id');

        $response = $this->actingAs($admin)->get(route('admin.payments.index'))->assertOk();
        $xpath = $this->htmlXPath($response->getContent());
        $submittedInput = $xpath->query('//*[@id="transaction_id-'.$second['payment']->id.'"]')->item(0);
        $otherInput = $xpath->query('//*[@id="transaction_id-'.$first['payment']->id.'"]')->item(0);

        $this->assertSame($transactionId, $submittedInput->getAttribute('value'));
        $this->assertSame('', $otherInput->getAttribute('value'));
        $this->assertSame('true', $submittedInput->getAttribute('aria-invalid'));
        $this->assertSame('', $otherInput->getAttribute('aria-invalid'));
        $this->assertDatabaseHas('payments', ['id' => $second['payment']->id, 'status' => 'pending']);
    }

    public function test_admin_payment_page_exposes_paid_and_refund_actions(): void
    {
        $this->context();
        $this->context('paid');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee(__('Mark paid'))
            ->assertSee(__('Refund'));
    }

    public function test_non_admin_cannot_transition_payments(): void
    {
        $context = $this->context();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.payments.transition', $context['payment']), ['status' => 'paid'])
            ->assertForbidden();

        $this->assertDatabaseHas('payments', ['id' => $context['payment']->id, 'status' => 'pending']);
    }
}
