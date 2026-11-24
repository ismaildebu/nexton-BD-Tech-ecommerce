<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePaymentRequest extends FormRequest
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
            'gateway' => ['nullable', 'string', Rule::in(['cod', 'bkash', 'nagad', 'rocket', 'card'])],
        ];
    }
}
