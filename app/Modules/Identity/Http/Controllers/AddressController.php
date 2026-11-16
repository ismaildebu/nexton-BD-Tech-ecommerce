<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\Identity\Http\Requests\StoreAddressRequest;
use App\Modules\Identity\Http\Requests\UpdateAddressRequest;
use App\Modules\Identity\Services\AddressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Address::class);

        return view('addresses.index', [
            'addresses' => $this->addresses->listFor($request->user()),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Address::class);

        return view('addresses.create');
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        Gate::authorize('create', Address::class);

        $this->addresses->create(
            $request->user(),
            $request->safe()->except('is_default'),
            $request->boolean('is_default'),
        );

        return redirect()->route('addresses.index')->with('status', __('Address added.'));
    }

    public function edit(Address $address): View
    {
        Gate::authorize('update', $address);

        return view('addresses.edit', ['address' => $address]);
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        $this->addresses->update(
            $request->user(),
            $address,
            $request->safe()->except('is_default'),
            $request->boolean('is_default'),
        );

        return redirect()->route('addresses.index')->with('status', __('Address updated.'));
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        Gate::authorize('delete', $address);

        $this->addresses->delete($request->user(), $address);

        return redirect()->route('addresses.index')->with('status', __('Address deleted.'));
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        $this->addresses->setDefault($request->user(), $address);

        return redirect()->route('addresses.index')->with('status', __('Default address changed.'));
    }
}
