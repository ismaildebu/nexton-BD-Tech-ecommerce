<?php

namespace App\Modules\Coupon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->is_admin;
    }

    public function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:64', 'unique:coupons,code'],
            'type' => ['required', 'string', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'minimum_order' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($this->input('type') === 'percent') {
            $rules['value'][] = 'max:100';
        }

        if ($this->filled('starts_at')) {
            $rules['expires_at'][] = 'after_or_equal:starts_at';
        }

        return $rules;
    }
}
