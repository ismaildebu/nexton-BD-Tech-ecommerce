<?php

namespace App\Modules\Subscription\Http\Requests;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $vendorId = $this->input('vendor_id');

        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (! is_numeric($vendorId)) {
            return false;
        }

        $vendor = Vendor::find((int) $vendorId);

        return $vendor !== null && $vendor->owner_user_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'plan' => ['nullable', 'string', 'max:64'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', Rule::when($this->filled('starts_at'), ['after:starts_at'])],
        ];
    }
}
