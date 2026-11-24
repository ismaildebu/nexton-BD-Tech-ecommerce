@extends('layouts.admin')
@section('title', __('Edit Category'))
@section('content')
    <div class="ui-page-narrow">
        <x-ui.page-header :title="__('Edit category').': '.$category->name" :description="__('Update the category details, hierarchy, and availability.')" :eyebrow="__('Catalog management')">
            <x-slot:action>
                <x-ui.button :href="route('admin.categories.index')" variant="outline">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
                    {{ __('Back to categories') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @include('admin.categories._form')
    </div>
@endsection
