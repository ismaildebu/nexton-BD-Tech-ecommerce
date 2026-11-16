<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Http\Requests\Concerns\NormalizesLoginIdentifiers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    use NormalizesLoginIdentifiers;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLoginIdentifiers();
    }

    /**
     * The user is always the authenticated user; no user id is ever read from the input.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required_without:phone', 'nullable', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required_without:email', 'nullable', 'string', 'regex:/^\+8801[3-9]\d{8}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'preferred_language' => ['nullable', 'string', Rule::in(['bn', 'en'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => __('Keep an email address or a phone number.'),
            'phone.required_without' => __('Keep an email address or a phone number.'),
            'phone.regex' => __('Enter a valid Bangladeshi mobile number, for example 01712345678.'),
        ];
    }
}
