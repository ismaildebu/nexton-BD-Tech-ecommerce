<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Http\Requests\ResendVerificationRequest;
use App\Modules\Identity\Http\Requests\VerifyPhoneRequest;
use App\Modules\Identity\Services\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LogicException;

class VerificationController extends Controller
{
    public function notice(Request $request, VerificationService $verification): View|RedirectResponse
    {
        $user = $this->verificationUser($request);

        if ($user === null) {
            return redirect()->route('login')->with('error', __('Start a verification request before continuing.'));
        }

        $verification->syncStatus($user);

        if ($user->status === UserStatus::Active) {
            $this->completeVerification($request, $user);

            return $this->verificationRedirect($request, $user);
        }

        return view('verification.notice', compact('user'));
    }

    public function verify(Request $request, int $id, string $hash, VerificationService $verification): RedirectResponse
    {
        $user = User::find($id);

        if ($user === null || ! $this->canVerify($user) || ! $verification->verifyEmail($user, $hash)) {
            return redirect()->route('login')->with('error', __('This email verification link is invalid or has expired.'));
        }

        $this->completeVerification($request, $user);

        return $this->verificationRedirect($request, $user);
    }

    public function phone(Request $request): View|RedirectResponse
    {
        $user = $this->verificationUser($request);

        if ($user === null) {
            return redirect()->route('login')->with('error', __('Start a verification request before continuing.'));
        }

        if ($user->phone === null || $user->hasVerifiedPhone()) {
            return redirect()->route('verification.notice')->with('error', __('This mobile number does not need verification.'));
        }

        return view('verification.phone', compact('user'));
    }

    public function verifyPhone(VerifyPhoneRequest $request, VerificationService $verification): RedirectResponse
    {
        $user = $this->verificationUser($request);

        if ($user === null) {
            return redirect()->route('login')->with('error', __('Start a verification request before continuing.'));
        }

        try {
            $verified = $verification->verifyPhone($user, $request->string('code')->toString());
        } catch (LogicException $exception) {
            report($exception);

            return back()->with('error', __('Phone verification is temporarily unavailable. Please try again later.'));
        }

        if (! $verified) {
            return back()->withErrors(['code' => __('The code is invalid or has expired.')]);
        }

        $this->completeVerification($request, $user);

        return $this->verificationRedirect($request, $user);
    }

    public function resend(ResendVerificationRequest $request, VerificationService $verification): RedirectResponse
    {
        $user = $this->verificationUser($request);

        if ($user === null) {
            return redirect()->route('login')->with('error', __('Start a verification request before continuing.'));
        }

        $channel = $request->string('channel')->toString();
        $available = $channel === 'email'
            ? $user->email !== null && ! $user->hasVerifiedEmail()
            : $user->phone !== null && ! $user->hasVerifiedPhone();

        if (! $available) {
            return back()->with('error', __('This contact does not need verification.'));
        }

        try {
            if ($channel === 'email') {
                $verification->sendEmail($user);
            } else {
                $verification->sendPhone($user);
            }
        } catch (LogicException $exception) {
            report($exception);

            return back()->with('error', __('The verification message could not be sent. Please try again later.'));
        }

        return back()->with('status', __('A new verification message has been sent.'));
    }

    private function verificationUser(Request $request): ?User
    {
        $authenticated = $request->user();

        if ($authenticated !== null && $this->canVerify($authenticated)) {
            return $authenticated;
        }

        $userId = $request->session()->get('verification_user_id');
        $user = $userId === null ? null : User::find($userId);

        return $user !== null && $this->canVerify($user) ? $user : null;
    }

    private function canVerify(User $user): bool
    {
        return in_array($user->status, [UserStatus::Active, UserStatus::PendingVerification], true);
    }

    private function completeVerification(Request $request, User $user): void
    {
        if ($request->user()?->is($user)) {
            return;
        }

        $request->session()->forget('verification_user_id');
        $request->session()->regenerate();
    }

    private function verificationRedirect(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return redirect()->route('profile.edit')->with('status', __('Your contact has been verified.'));
        }

        return redirect()->route('login')->with('status', __('Your account is verified. Please sign in.'));
    }
}
