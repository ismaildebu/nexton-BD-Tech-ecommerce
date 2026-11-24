<?php

namespace App\Modules\Commission\Http\Requests;

use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'amount' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'required_without:rate',
                Rule::when(! $this->filled('order_id'), ['required']),
            ],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('order_id')) {
                return;
            }

            $order = Order::with('items.product')->find($this->integer('order_id'));
            $base = $this->vendorOrderTotal($order, $this->integer('vendor_id'));

            if ($order === null || $base <= 0) {
                $validator->errors()->add('order_id', __('The selected order has no items for this vendor.'));

                return;
            }

            if ($this->filled('rate')) {
                $derived = round($base * ((float) $this->input('rate') / 100), 2);

                if ($derived < 0.01) {
                    $validator->errors()->add('rate', __('The rate must produce a positive commission amount.'));
                }

                if ($this->filled('amount') && abs((float) $this->input('amount') - $derived) > 0.009) {
                    $validator->errors()->add('amount', __('For an order-linked commission, the amount must equal the vendor order total multiplied by the rate.'));
                }
            } elseif ((float) $this->input('amount') > $base) {
                $validator->errors()->add('amount', __('An order-linked commission cannot exceed the vendor order total.'));
            }
        });
    }

    private function vendorOrderTotal(?Order $order, int $vendorId): float
    {
        if ($order === null) {
            return 0.0;
        }

        return (float) $order->items
            ->filter(fn ($item) => $item->product?->vendor_id === $vendorId)
            ->sum('line_total');
    }
}
