<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Support\Facades\Gate;

class UpdateAddressRequest extends StoreAddressRequest
{
    /**
     * Ownership is checked by AddressPolicy. Another user's address is answered with 404.
     */
    public function authorize(): bool
    {
        Gate::inspect('update', $this->route('address'))->authorize();

        return true;
    }
}
