<?php

namespace App\Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['paid', 'refunded'])],
            'transaction_id' => ['nullable', 'string', 'max:128'],
        ];
    }
}
