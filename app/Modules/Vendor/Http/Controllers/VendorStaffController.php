<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorStaffController extends Controller
{
    public function index(Vendor $vendor): View
    {
        return view('vendor.staff.index', ['vendor' => $vendor, 'staff' => $vendor->staff()->with('user')->get()]);
    }

    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'staff_role' => ['required', 'string', 'max:64'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $vendor->staff()->create([
            'user_id' => $user->getKey(),
            'staff_role' => $data['staff_role'],
            'status' => 'active',
            'invited_by' => $request->user()->getKey(),
            'joined_at' => now(),
        ]);

        return redirect()->route('vendor.staff.index', $vendor)->with('status', __('Staff added.'));
    }
}
