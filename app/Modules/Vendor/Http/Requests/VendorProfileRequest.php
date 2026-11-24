<?php

namespace App\Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorProfileRequest extends VendorRegistrationRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageProfile', $this->route('vendor')) ?? false;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'business_email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('vendors', 'business_email')->ignore($this->route('vendor')),
            ],
        ]);
    }
}
