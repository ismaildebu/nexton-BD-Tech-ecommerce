<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\UpdatePasswordRequest;
use App\Modules\Identity\Http\Requests\UpdateProfileRequest;
use App\Modules\Identity\Services\UserProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use LogicException;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        Gate::authorize('view', $user);

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->customerProfile,
        ]);
    }

    public function update(UpdateProfileRequest $request, UserProfileService $profiles): RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('update', $user);

        try {
            $profiles->updateProfile($user, $request->validated());
        } catch (LogicException|ValidationException $exception) {
            if ($exception instanceof LogicException) {
                report($exception);
            }

            return redirect()->route('profile.edit')
                ->with('status', __('Profile saved.'))
                ->with('error', __('The verification message could not be sent. Please try again from the verification page.'));
        }

        return redirect()->route('profile.edit')->with('status', __('Profile saved.'));
    }

    public function updatePassword(UpdatePasswordRequest $request, UserProfileService $profiles): RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('update', $user);

        $profiles->changePassword(
            $user,
            $request->string('current_password')->toString(),
            $request->string('password')->toString(),
            $request->session()->getId(),
        );

        return redirect()->route('profile.edit')->with('status', __('Password changed.'));
    }
}
