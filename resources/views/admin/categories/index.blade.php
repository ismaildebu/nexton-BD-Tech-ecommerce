@extends('layouts.admin')
@section('title', __('Categories'))
@section('content')
    <div class="ui-page">
        <x-ui.page-header
            :title="__('Categories')"
            :description="__('Organize the storefront catalog. Only active categories can be selected on new or updated products.')"
            :eyebrow="__('Catalog management')"
        >
            <x-slot:action>
                <x-ui.button :href="route('admin.categories.create')" variant="primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Create category') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if ($categories->count())
            <x-ui.table :caption="__('Product categories')" class="[&_table]:min-w-[820px]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Slug') }}</th>
                        <th scope="col">{{ __('Parent') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>

                @foreach ($categories as $category)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h6l2 2h10v10H3V7z"/></svg>
                                </span>
                                <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                            </div>
                        </td>
                        <td class="font-mono text-xs text-slate-500">{{ $category->slug }}</td>
                        <td>{{ $category->parent->name ?? '—' }}</td>
                        <td><x-ui.status>{{ $category->is_active ? __('Active') : __('Inactive') }}</x-ui.status></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                <x-ui.button :href="route('admin.categories.edit', $category)" variant="outline" size="sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    {{ __('Edit') }}
                                </x-ui.button>
                                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" :variant="$category->is_active ? 'outline' : 'success'" size="sm" :class="$category->is_active ? 'border-rose-200 text-rose-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700' : ''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                                        {{ $category->is_active ? __('Deactivate') : __('Activate') }}
                                    </x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @else
            <x-ui.card :padding="false">
                <x-ui.empty-state :title="__('No categories found')" :description="__('Create a category to begin organizing your product catalog.')">
                    <x-slot:action>
                        <x-ui.button :href="route('admin.categories.create')" variant="primary">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Create category') }}
                        </x-ui.button>
                    </x-slot:action>
                    <x-slot:icon>
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h6l2 2h10v10H3V7z"/></svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </x-ui.card>
        @endif

        @if ($categories->hasPages())
            <div class="ui-pagination mt-6">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
