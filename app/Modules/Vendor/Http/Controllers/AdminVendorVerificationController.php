<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorStatusHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminVendorVerificationController extends Controller
{
    public function index(): View
    {
        return view('admin.vendors.index', [
            'vendors' => Vendor::query()->latest()->paginate(20),
        ]);
    }

    public function show(Vendor $vendor): View
    {
        return view('admin.vendors.show', ['vendor' => $vendor]);
    }

    public function approve(Request $request, Vendor $vendor): RedirectResponse
    {
        $previousVerification = $vendor->verification_status?->value;
        $previousStatus = $vendor->status?->value;

        $vendor->forceFill([
            'verification_status' => 'approved',
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $request->user()->getKey(),
        ])->save();

        VendorStatusHistory::create([
            'vendor_id' => $vendor->getKey(),
            'previous_verification_status' => $previousVerification,
            'new_verification_status' => 'approved',
            'previous_account_status' => $previousStatus,
            'new_account_status' => 'active',
            'changed_by' => $request->user()->getKey(),
            'reason' => 'Approved',
        ]);

        return redirect()->route('admin.vendors.index')->with('status', __('Vendor approved.'));
    }

    public function reject(Request $request, Vendor $vendor): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $previousVerification = $vendor->verification_status?->value;
        $previousStatus = $vendor->status?->value;

        $vendor->forceFill([
            'verification_status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->getKey(),
            'rejection_reason' => $request->string('reason')->toString(),
        ])->save();

        VendorStatusHistory::create([
            'vendor_id' => $vendor->getKey(),
            'previous_verification_status' => $previousVerification,
            'new_verification_status' => 'rejected',
            'previous_account_status' => $previousStatus,
            'new_account_status' => $vendor->status?->value,
            'changed_by' => $request->user()->getKey(),
            'reason' => $request->string('reason')->toString(),
        ]);

        return redirect()->route('admin.vendors.index')->with('status', __('Vendor rejected.'));
    }
}
