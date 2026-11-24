<?php

namespace App\Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Http\Requests\CreateSubscriptionRequest;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Subscription\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use DomainException;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(Request $request): View
    {
        $subscriptions = Subscription::whereHas('vendor', fn ($q) => $q->where('owner_user_id', $request->user()->id))->latest()->paginate(20);

        return view('subscriptions.index', ['subscriptions' => $subscriptions]);
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

        return redirect()->route('subscriptions.index')->with('status', __('Subscription created.'));
    }
}
