@extends('layouts.admin')
@section('title', __('Create CMS Page'))
@section('content')
    <div class="ui-page-narrow">
        <x-ui.page-header :title="__('Create CMS page')" :description="__('Published pages appear in the storefront footer and at their public URL.')" :eyebrow="__('Content management')">
            <x-slot:action>
                <x-ui.button :href="route('admin.cms.index')" variant="outline">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
                    {{ __('Back to CMS pages') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <x-ui.card :padding="false">
            <form method="POST" action="{{ route('cms.store') }}" class="space-y-6 p-5 sm:p-6">
                @csrf
                <x-form.input name="title" :label="__('Title')" maxlength="255" required />
                <x-form.input name="slug" :label="__('Slug')" :hint="__('Spaces and uppercase letters are normalized to lowercase hyphens.')" maxlength="160" required class="font-mono" />

                <x-form.textarea name="content" :label="__('Content')" :hint="__('Add the information customers should see on this page. Up to 50,000 characters.')" rows="12">{{ old('content') }}</x-form.textarea>

                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="is_published" value="0">
                    <div class="flex items-start gap-3">
                        <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published')) @error('is_published') aria-invalid="true" @enderror aria-describedby="is_published-help{{ $errors->has('is_published') ? ' is_published-error' : '' }}" class="mt-1 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <label for="is_published" class="text-sm font-semibold leading-5 text-slate-700">{{ __('Publish immediately') }}</label>
                            <p id="is_published-help" class="ui-help">{{ __('Published pages are publicly visible. Leave unchecked to save a draft.') }}</p>
                        </div>
                    </div>
                    @error('is_published')
                        <p id="is_published-error" class="ui-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button :href="route('admin.cms.index')" variant="outline">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                        {{ __('Create page') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
