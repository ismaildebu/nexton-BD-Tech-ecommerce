@extends('layouts.admin')

@section('title', __('Site settings'))

@section('content')
    <x-ui.page-header
        :title="__('Site settings')"
        :eyebrow="__('Store appearance & content')"
        :description="__('Update the branding and global information shown across your storefront.')"
    >
        <x-slot:action>
            <x-ui.button :href="url('/')" target="_blank" rel="noopener" variant="outline" size="sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                {{ __('View store') }}
            </x-ui.button>
        </x-slot:action>
    </x-ui.page-header>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-7 max-w-7xl space-y-6" x-data="appearancePreview(@js($appearancePreview))">
        @csrf
        @method('PUT')

        <x-ui.card :padding="false">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Design system') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Choose a preset or customize the colors, typography, spacing and layout used across the site.') }}</p>
            </div>
            <div class="grid gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div class="space-y-6">
                    <x-form.select name="appearance_preset" :label="__('Design preset')" :hint="__('Applying a preset updates the controls below. Save to publish it.')" required x-model="preset" @change="applyPreset()">
                        @foreach ($appearanceOptions['presets'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('appearance_preset', $appearance['appearance_preset']) === $value)>{{ __($label) }}</option>
                        @endforeach
                    </x-form.select>

                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <x-form.input name="primary_color" type="color" :label="__('Primary color')" :value="$appearance['primary_color']" required x-model="primaryColor" @input="preset = 'custom'" />
                            <div class="mt-3 flex gap-1" aria-hidden="true">
                                <template x-for="color in primaryPalette" :key="color"><span class="h-5 flex-1 rounded-sm" x-bind:style="{ backgroundColor: color }"></span></template>
                            </div>
                        </div>
                        <div>
                            <x-form.input name="accent_color" type="color" :label="__('Accent color')" :value="$appearance['accent_color']" required x-model="accentColor" @input="preset = 'custom'" />
                            <div class="mt-3 flex gap-1" aria-hidden="true">
                                <template x-for="color in accentPalette" :key="color"><span class="h-5 flex-1 rounded-sm" x-bind:style="{ backgroundColor: color }"></span></template>
                            </div>
                        </div>
                        <div>
                            <x-form.input name="neutral_color" type="color" :label="__('Neutral color')" :value="$appearance['neutral_color']" required x-model="neutralColor" @input="preset = 'custom'" />
                            <p class="ui-help">{{ __('Controls text, borders and surfaces.') }}</p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.select name="font_family" :label="__('Font family')" required x-model="fontFamily" @change="preset = 'custom'">
                            @foreach ($appearanceOptions['fonts'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('font_family', $appearance['font_family']) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="density" :label="__('Layout density')" required x-model="density" @change="preset = 'custom'">
                            @foreach ($appearanceOptions['densities'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('density', $appearance['density']) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-form.input name="border_radius" type="range" :label="__('Corner radius')" :value="$appearance['border_radius']" min="0" max="24" step="1" required x-model.number="borderRadius" @input="preset = 'custom'" />
                            <output for="border_radius" class="ui-help" x-text="`${borderRadius}px`">{{ $appearance['border_radius'] }}px</output>
                        </div>
                        <div>
                            <x-form.input name="container_width" type="range" :label="__('Content width')" :value="$appearance['container_width']" min="1120" max="1536" step="8" required x-model.number="containerWidth" @input="preset = 'custom'" />
                            <output for="container_width" class="ui-help" x-text="`${containerWidth}px`">{{ $appearance['container_width'] }}px</output>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.select name="header_style" :label="__('Header style')" required x-model="headerStyle" @change="preset = 'custom'">
                            @foreach ($appearanceOptions['header_styles'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('header_style', $appearance['header_style']) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.select name="footer_style" :label="__('Footer style')" required x-model="footerStyle" @change="preset = 'custom'">
                            @foreach ($appearanceOptions['footer_styles'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('footer_style', $appearance['footer_style']) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <x-form.select name="card_style" :label="__('Card style')" :hint="__('Applies to shared cards, tables and content panels.')" required x-model="cardStyle" @change="preset = 'custom'">
                        @foreach ($appearanceOptions['card_styles'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('card_style', $appearance['card_style']) === $value)>{{ __($label) }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <aside class="self-start lg:sticky lg:top-24" aria-label="{{ __('Live preview') }}">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold text-slate-900">{{ __('Live preview') }}</h3>
                        <span class="ui-badge ui-badge-neutral">{{ __('Unsaved changes') }}</span>
                    </div>
                    <div class="appearance-preview" x-bind:style="previewStyle" x-bind:data-header-style="headerStyle" x-bind:data-footer-style="footerStyle" x-bind:data-card-style="cardStyle">
                        <div class="preview-header">
                            <div class="preview-logo text-sm font-black">{{ $siteSettings->site_name }}</div>
                            <div class="preview-nav hidden sm:flex items-center gap-3 text-[10px] font-semibold">
                                <span>{{ __('Shop') }}</span><span>{{ __('Categories') }}</span>
                            </div>
                            <span class="h-6 w-6 rounded-full bg-brand-500"></span>
                        </div>
                        <div class="preview-content">
                            <p class="ui-eyebrow">{{ __('Curated technology') }}</p>
                            <h4 class="text-xl font-extrabold" x-bind:style="{ fontFamily: previewFont }">{{ __('A better way to shop online.') }}</h4>
                            <p class="mt-2 text-xs leading-5 text-slate-500">{{ __('Discover trusted merchants and products selected for everyday life.') }}</p>
                            <span class="ui-btn ui-btn-primary ui-btn-sm mt-4">{{ __('Shop now') }}</span>
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                @foreach ([1, 2] as $item)
                                    <div class="preview-card">
                                        <div class="mb-3 h-16 rounded-lg bg-gradient-to-br from-brand-100 to-accent-100"></div>
                                        <p class="text-[10px] font-bold">{{ __('Featured product') }} {{ $item }}</p>
                                        <p class="mt-1 text-[10px] text-slate-500">৳{{ number_format(2490 * $item) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="preview-footer">
                            <p class="text-xs font-bold">{{ $siteSettings->site_name }}</p>
                            <div class="preview-footer-links mt-2 flex gap-3 text-[10px] font-medium">
                                <span>{{ __('Products') }}</span><span>{{ __('Support') }}</span><span>{{ __('My account') }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-slate-500">{{ __('Preview updates instantly. The storefront changes after you save settings.') }}</p>
                </aside>
            </div>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Branding') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Your store name and logo appear in navigation, page titles and shared layouts.') }}</p>
            </div>
            <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div>
                    <x-form.input name="site_name" :label="__('Site name')" :value="$settings->site_name" maxlength="120" required autocomplete="organization" />
                </div>
                <div>
                    <x-form.input
                        name="logo"
                        type="file"
                        :label="__('Logo')"
                        accept="image/jpeg,image/png,image/webp"
                        :hint="__('JPG, PNG or WebP. Maximum 1 MB and 2000 × 2000 pixels.')"
                    />

                    @if ($settings->logo_url)
                        <div class="mt-4 flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_name }}" class="h-16 w-16 rounded-xl bg-white object-contain ring-1 ring-slate-200">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-700">{{ __('Current logo') }}</p>
                                <p class="mt-1 break-all text-xs text-slate-400">{{ basename($settings->logo_path) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="remove_logo" value="0">
                                <input id="remove_logo" type="checkbox" name="remove_logo" value="1" @checked(old('remove_logo', false)) @error('remove_logo') aria-invalid="true" @enderror class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                <label for="remove_logo" class="text-xs font-semibold text-slate-600">{{ __('Remove logo') }}</label>
                            </div>
                        </div>
                    @endif
                    @error('remove_logo')
                        <p class="ui-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Announcement') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Shown in the bar above the storefront navigation. Leave empty to hide it.') }}</p>
            </div>
            <div class="p-5 sm:p-6">
                <x-form.textarea name="announcement" :label="__('Announcement text')" rows="2" maxlength="255">{{ $settings->announcement }}</x-form.textarea>
            </div>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Contact information') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Displayed in the storefront footer for customer support.') }}</p>
            </div>
            <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <x-form.input name="contact_email" type="email" :label="__('Support email')" :value="$settings->contact_email" maxlength="150" autocomplete="email" />
                <x-form.input name="contact_phone" type="tel" :label="__('Support phone')" :value="$settings->contact_phone" maxlength="40" autocomplete="tel" />
            </div>
        </x-ui.card>

        <x-ui.card :padding="false">
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <h2 class="ui-section-title">{{ __('Footer') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Describe your store and set the copyright line shown at the bottom of the storefront.') }}</p>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <x-form.textarea name="footer_description" :label="__('Store description')" rows="4" maxlength="1000">{{ $settings->footer_description }}</x-form.textarea>
                <x-form.input
                    name="copyright_text"
                    :label="__('Copyright text')"
                    :value="$settings->copyright_text"
                    maxlength="255"
                    required
                    :hint="__('The current year and copyright symbol are added automatically.')"
                />
            </div>
        </x-ui.card>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <x-ui.button :href="route('admin.dashboard')" variant="ghost">{{ __('Back to dashboard') }}</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                {{ __('Save settings') }}
            </x-ui.button>
        </div>
    </form>
@endsection
