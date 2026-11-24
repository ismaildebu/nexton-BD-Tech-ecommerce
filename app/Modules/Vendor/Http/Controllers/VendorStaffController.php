<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorUser;
use App\Modules\Vendor\Services\StaffInvitationService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorStaffController extends Controller
{
    public function __construct(private readonly StaffInvitationService $invitations) {}

    public function index(Vendor $vendor): View
    {
        Gate::authorize('manageStaff', $vendor);

        return view('vendor.staff.index', [
            'vendor' => $vendor,
            'staff' => $vendor->staff()->with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        Gate::authorize('manageStaff', $vendor);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'staff_role' => ['required', Rule::in(['manager', 'staff'])],
        ]);

        $invitation = $this->invitations->invite($vendor, $request->user(), $data['email'], $data['staff_role']);

        return redirect()->route('vendor.staff.index', $vendor)
            ->with('status', __('Invitation created.'))
            ->with('invitation_token', $invitation['token']);
    }

    public function showInvitation(Request $request, string $token): View
    {
        try {
            $invitation = $this->invitations->findByToken($token);
        } catch (DomainException $exception) {
            abort(410, $exception->getMessage());
        }

        return view('vendor.staff.invitation', [
            'invitation' => $invitation,
            'token' => $token,
            'isInvitee' => $request->user()->id === $invitation->user_id,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        try {
            $this->invitations->accept($token, $request->user());
        } catch (DomainException $exception) {
            return redirect()->route('vendor.staff.invitation', $token)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.products.index')->with('status', __('Invitation accepted.'));
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        try {
            $this->invitations->decline($token, $request->user());
        } catch (DomainException $exception) {
            return redirect()->route('vendor.staff.invitation', $token)->with('error', $exception->getMessage());
        }

        return redirect()->route('profile.edit')->with('status', __('Invitation declined.'));
    }

    public function revoke(Request $request, Vendor $vendor, VendorUser $vendorUser): RedirectResponse
    {
        Gate::authorize('manageStaff', $vendor);

        try {
            $this->invitations->revoke($vendor, $vendorUser);
        } catch (DomainException $exception) {
            return redirect()->route('vendor.staff.index', $vendor)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor.staff.index', $vendor)->with('status', __('Invitation revoked.'));
    }
}
