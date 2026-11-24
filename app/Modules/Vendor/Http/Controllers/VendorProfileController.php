<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Http\Requests\VendorProfileRequest;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VendorProfileController extends Controller
{
    public function edit(Vendor $vendor): View
    {
        Gate::authorize('manageProfile', $vendor);

        return view('vendor.settings.edit', ['vendor' => $vendor]);
    }

    public function update(VendorProfileRequest $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($request->validated());

        return redirect()->route('vendor.settings.edit', $vendor)->with('status', __('Business profile updated.'));
    }
}
