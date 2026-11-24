<?php

namespace App\Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdminShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'carrier' => ['required', 'string', 'max:128'],
            'tracking_number' => ['required', 'string', 'max:128'],
        ];
    }
}
