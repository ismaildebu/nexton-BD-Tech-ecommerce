<?php

namespace App\Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdminNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'max:255'],
            'data' => ['required', 'array'],
            'data.message' => ['required', 'string', 'max:2000'],
            'data.url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
