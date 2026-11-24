<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\RegisterUserRequest;
use App\Modules\Identity\Services\AuthenticationService;
use App\Modules\Identity\Services\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use LogicException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request, AuthenticationService $auth, VerificationService $verification): RedirectResponse
    {
        $user = $auth->register($request->validated());
        $sendFailed = false;

        try {
            $verification->sendInitialChallenges($user);
        } catch (LogicException $exception) {
            report($exception);
            $sendFailed = true;
        }

        $request->session()->regenerate();
        $request->session()->put('verification_user_id', $user->getKey());

        $redirect = redirect()->route('verification.notice')
            ->with('status', __('Complete email or mobile verification to sign in.'));

        if ($sendFailed) {
            $redirect->with('error', __('Your account was created, but a verification message could not be sent. Please try resending it.'));
        }

        return $redirect;
    }
}
