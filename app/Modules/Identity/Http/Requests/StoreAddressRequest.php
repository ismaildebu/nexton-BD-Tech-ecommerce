<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Enums\AddressType;
use App\Modules\Identity\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        if (is_string($phone)) {
            $trimmed = trim($phone);
            $this->merge(['phone' => PhoneNumber::normalize($trimmed) ?? $trimmed]);
        }

        $postalCode = $this->input('postal_code');

        if (is_string($postalCode) && trim($postalCode) === '') {
            $this->merge(['postal_code' => null]);
        }

        $upazila = $this->input('upazila');

        if (is_string($upazila) && trim($upazila) === '') {
            $this->merge(['upazila' => null]);
        }
    }

    /**
     * user_id and is_default ownership data are never accepted as address fields.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AddressType::class)],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+8801[3-9]\d{8}$/'],
            'address_line' => ['required', 'string', 'max:500'],
            'division' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, for example 01712345678.'),
            'postal_code.regex' => __('The postal code must be 4 digits.'),
        ];
    }
}
