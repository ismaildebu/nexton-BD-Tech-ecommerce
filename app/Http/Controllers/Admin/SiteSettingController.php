<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\Appearance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $settings = SiteSetting::current();
        $appearance = array_merge(
            $settings->only(Appearance::FIELDS),
            Arr::only(old(), Appearance::FIELDS),
        );

        return view('admin.settings.edit', [
            'settings' => $settings,
            'appearance' => $appearance,
            'appearanceOptions' => Appearance::options(),
            'appearancePreview' => Appearance::previewConfig($appearance),
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::current();
        $data = $request->validated();
        $previousLogo = $settings->logo_path;

        unset($data['remove_logo'], $data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('site-settings', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $data['logo_path'] = null;
        }

        $settings->update($data);

        if ($previousLogo && $previousLogo !== $settings->logo_path) {
            Storage::disk('public')->delete($previousLogo);
        }

        return redirect()->route('admin.settings.edit')->with('status', __('Site settings updated.'));
    }
}
