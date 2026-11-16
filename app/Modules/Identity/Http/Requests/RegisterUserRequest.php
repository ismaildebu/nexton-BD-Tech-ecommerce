<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Http\Requests\Concerns\NormalizesLoginIdentifiers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    use NormalizesLoginIdentifiers;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLoginIdentifiers();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required_without:phone', 'nullable', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required_without:email', 'nullable', 'string', 'regex:/^\+8801[3-9]\d{8}$/', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => __('Enter an email address or a phone number.'),
            'phone.required_without' => __('Enter an email address or a phone number.'),
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, for example 01712345678.'),
        ];
    }
}
