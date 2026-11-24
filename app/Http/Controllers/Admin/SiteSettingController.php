<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use App\Support\Appearance;
use App\Support\HomepageDesigns;
use App\Support\HomepageSections;
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
        $homepageDesign = HomepageDesigns::sanitize(old('homepage_design', $settings->homepageDesign()));

        return view('admin.settings.edit', [
            'settings' => $settings,
            'appearance' => $appearance,
            'appearanceOptions' => Appearance::options(),
            'appearancePreview' => Appearance::previewConfig($appearance),
            'homepageDesign' => $homepageDesign,
            'homepageDesignOptions' => HomepageDesigns::presets(),
            'homepageSections' => $settings->homepageSections(),
            'homepageSectionOptions' => HomepageSections::sections(),
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::current();
        $data = $request->validated();
        $previousLogo = $settings->logo_path;

        unset($data['remove_logo'], $data['logo']);

        $currentDesign = $settings->homepageDesign();
        $sections = $settings->homepageSections();
        $designChanged = false;

        if ($request->has('homepage_sections')) {
            $sections = HomepageSections::sanitize($data['homepage_sections'] ?? []);
        }

        if ($request->has('homepage_design')) {
            $design = HomepageDesigns::sanitize($data['homepage_design']);
            $data['homepage_design'] = $design;
            $designChanged = $design !== $currentDesign;

            if ($designChanged) {
                $sections = HomepageDesigns::applyLayouts($sections, $design);
            }
        }

        if ($request->has('homepage_sections') || $designChanged) {
            $data['homepage_sections'] = $sections;
        }

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
