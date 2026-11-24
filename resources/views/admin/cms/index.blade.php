@extends('layouts.admin')
@section('title', __('CMS Pages'))
@section('content')
    <div class="ui-page">
        <x-ui.page-header
            :title="__('CMS Pages')"
            :description="__('Create and manage storefront information pages.')"
            :eyebrow="__('Content management')"
        >
            <x-slot:action>
                <x-ui.button :href="route('admin.cms.create')" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Create page') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if ($pages->count())
            <x-ui.table :caption="__('Storefront CMS pages')">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Title') }}</th>
                        <th scope="col">{{ __('Slug') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Updated') }}</th>
                        <th scope="col" class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($pages as $page)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </span>
                                <p class="font-semibold text-slate-900">{{ $page->title }}</p>
                            </div>
                        </td>
                        <td class="font-mono text-xs text-slate-500">/{{ $page->slug }}</td>
                        <td><x-ui.status>{{ $page->is_published ? __('Published') : __('Draft') }}</x-ui.status></td>
                        <td class="whitespace-nowrap text-xs text-slate-500">
                            @if ($page->updated_at)
                                <time datetime="{{ $page->updated_at->toIso8601String() }}">{{ $page->updated_at->format('d M Y') }}</time>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($page->is_published)
                                <x-ui.button :href="route('cms.show', $page->slug)" target="_blank" rel="noopener" variant="outline" size="sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    {{ __('View page') }}
                                </x-ui.button>
                            @else
                                <span class="text-xs text-slate-400">{{ __('Draft pages are not public.') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No CMS pages found')" :description="__('Create an information page for your storefront footer or public URL.')">
                    <x-slot:action>
                        <x-ui.button :href="route('admin.cms.create')" variant="primary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Create page') }}
                        </x-ui.button>
                    </x-slot:action>
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($pages->hasPages())
            <div class="ui-pagination mt-6">{{ $pages->links() }}</div>
        @endif
    </div>
@endsection
