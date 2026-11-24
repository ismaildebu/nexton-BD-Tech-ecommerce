<?php

namespace App\Http\Requests\Admin;

use App\Support\Appearance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    protected function prepareForValidation(): void
    {
        $fields = ['site_name', 'contact_email', 'contact_phone', 'announcement', 'footer_description', 'copyright_text', ...Appearance::FIELDS];
        $values = [];

        foreach ($fields as $field) {
            if (is_string($this->input($field))) {
                $values[$field] = trim($this->input($field));
            }
        }

        foreach (['primary_color', 'accent_color', 'neutral_color'] as $field) {
            if (isset($values[$field])) {
                $values[$field] = strtolower($values[$field]);
            }
        }

        if ($values !== []) {
            $this->merge($values);
        }
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'remove_logo' => ['nullable', 'boolean'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'announcement' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'copyright_text' => ['required', 'string', 'max:255'],
            'appearance_preset' => ['required', Rule::in(array_keys(Appearance::presets()))],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'neutral_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['required', Rule::in(array_keys(Appearance::fonts()))],
            'border_radius' => ['required', 'integer', 'between:0,24'],
            'container_width' => ['required', 'integer', 'between:1120,1536'],
            'density' => ['required', Rule::in(['compact', 'comfortable', 'spacious'])],
            'header_style' => ['required', Rule::in(['classic', 'centered', 'minimal'])],
            'footer_style' => ['required', Rule::in(['classic', 'minimal', 'dark'])],
            'card_style' => ['required', Rule::in(['soft', 'elevated', 'minimal'])],
        ];
    }
}
