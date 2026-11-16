<?php

namespace App\Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:160', 'unique:cms_pages,slug'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
