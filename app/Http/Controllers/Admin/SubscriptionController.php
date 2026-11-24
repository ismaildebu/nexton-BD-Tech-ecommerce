<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Http\Requests\CreateSubscriptionRequest;
use App\Modules\Subscription\Http\Requests\TransitionSubscriptionRequest;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Subscription\Services\SubscriptionService;
use App\Modules\Vendor\Models\Vendor;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::with('vendor')->latest()->paginate(20),
            'vendors' => Vendor::orderBy('business_name')->get(['id', 'business_name']),
        ]);
    }

    public function store(CreateSubscriptionRequest $request): RedirectResponse
    {
        try {
            $this->subscriptions->create(
                $request->integer('vendor_id'),
                $request->input('plan'),
                'inactive',
                $request->input('starts_at'),
                $request->input('expires_at'),
            );
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.subscriptions.index')->with('status', __('Subscription created.'));
    }

    public function transition(TransitionSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        try {
            $this->subscriptions->transition(
                $subscription,
                $request->string('status')->toString(),
                $request->input('starts_at'),
                $request->input('expires_at'),
            );
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('status', __('Subscription status updated.'));
    }
}
