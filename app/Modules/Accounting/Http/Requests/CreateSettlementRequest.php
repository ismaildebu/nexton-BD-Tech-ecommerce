<?php

namespace App\Modules\Accounting\Http\Requests;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;

class CreateSettlementRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ];
    }
}
