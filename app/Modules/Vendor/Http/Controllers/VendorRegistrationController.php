<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use App\Modules\Vendor\Http\Requests\VendorRegistrationRequest;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Services\VendorRegistrationService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorRegistrationController extends Controller
{
    public function __construct(private readonly VendorRegistrationService $registration) {}

    public function create(Request $request): View|RedirectResponse
    {
        $vendor = Vendor::where('owner_user_id', $request->user()->getKey())->first();

        if ($vendor !== null && ! in_array($vendor->verification_status, [VendorVerificationStatus::Draft, VendorVerificationStatus::Rejected], true)) {
            return redirect()->route('vendor.registration.status');
        }

        return view('vendor.registration.create', ['vendor' => $vendor]);
    }

    public function store(VendorRegistrationRequest $request): RedirectResponse
    {
        $existing = Vendor::where('owner_user_id', $request->user()->getKey())->first();
        $canUpdate = $existing !== null && in_array(
            $existing->verification_status,
            [VendorVerificationStatus::Draft, VendorVerificationStatus::Rejected],
            true
        );

        $this->registration->register($request->user(), $request->validated());

        $message = $existing !== null && ! $canUpdate
            ? __('An application already exists for this account.')
            : __('Application submitted.');

        return redirect()->route('vendor.registration.status')->with('status', $message);
    }

    public function status(Request $request): View
    {
        return view('vendor.registration.status', ['vendor' => Vendor::where('owner_user_id', $request->user()->getKey())->first()]);
    }

    public function resubmit(Request $request): RedirectResponse
    {
        $vendor = Vendor::where('owner_user_id', $request->user()->getKey())->first();
        abort_if($vendor === null, 404);
        Gate::authorize('manageProfile', $vendor);

        try {
            $this->registration->resubmit($vendor);
        } catch (DomainException $exception) {
            return redirect()->route('vendor.registration.status')->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.registration.status')->with('status', __('Corrected application resubmitted.'));
    }
}
