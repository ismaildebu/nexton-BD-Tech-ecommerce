<?php

namespace App\Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:32'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'business_address' => ['nullable', 'string', 'max:2000'],
            'district' => ['nullable', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'business_description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
