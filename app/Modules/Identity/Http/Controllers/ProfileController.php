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

        $profiles->updateProfile($user, $request->validated());

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
