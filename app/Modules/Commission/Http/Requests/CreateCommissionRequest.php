<?php

namespace App\Modules\Commission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
