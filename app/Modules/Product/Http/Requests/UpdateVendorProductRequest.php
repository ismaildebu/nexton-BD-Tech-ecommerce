<?php

namespace App\Modules\Product\Http\Requests;

class UpdateVendorProductRequest extends StoreVendorProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product')) ?? false;
    }
}