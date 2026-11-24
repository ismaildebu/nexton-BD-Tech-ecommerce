<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\ForgotPasswordRequest;
use App\Modules\Identity\Http\Requests\PhonePasswordResetRequest;
use App\Modules\Identity\Http\Requests\ResetPasswordRequest;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LogicException;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function createPhone(): View
    {
        return view('auth.phone-reset-password');
    }

    /**
     * The same message is returned whether or not the account exists.
     */
    public function store(ForgotPasswordRequest $request, AuthenticationService $auth): RedirectResponse
    {
        try {
            $auth->sendPasswordResetLink($request->string('login')->toString());
        } catch (ValidationException|LogicException $exception) {
            if ($exception instanceof LogicException) {
                report($exception);
            }
        }

        return back()->with('status', __('If an account with that email or mobile number exists, password reset instructions have been sent.'));
    }

    public function updatePhone(PhonePasswordResetRequest $request, AuthenticationService $auth): RedirectResponse
    {
        try {
            $reset = $auth->resetPasswordWithPhone(
                $request->string('phone')->toString(),
                $request->string('code')->toString(),
                $request->string('password')->toString(),
            );
        } catch (LogicException $exception) {
            report($exception);

            return back()
                ->withInput($request->only('phone'))
                ->withErrors(['code' => __('Phone verification is temporarily unavailable. Please try again later.')]);
        }

        if (! $reset) {
            return back()
                ->withInput($request->only('phone'))
                ->withErrors(['code' => __('The mobile number or code is invalid or has expired.')]);
        }

        return redirect()->route('login')->with('status', __('Your password has been reset. Please sign in.'));
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(ResetPasswordRequest $request, AuthenticationService $auth): RedirectResponse
    {
        $reset = $auth->resetPassword([
            'token' => $request->string('token')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'password_confirmation' => $request->string('password_confirmation')->toString(),
        ]);

        if (! $reset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('This password reset link is invalid or has expired.')]);
        }

        return redirect()->route('login')->with('status', __('Your password has been reset. Please sign in.'));
    }
}
