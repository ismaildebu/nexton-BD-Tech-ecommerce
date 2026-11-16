@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('CMS Pages') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Title') }}</th><th class="p-2">{{ __('Slug') }}</th><th class="p-2">{{ __('Published') }}</th></tr></thead>
        <tbody>
            @foreach ($pages as $p)
                <tr class="border-t"><td class="p-2">{{ $p->title }}</td><td class="p-2">{{ $p->slug }}</td><td class="p-2">{{ $p->is_published ? __('Yes') : __('No') }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $pages->links() }}</div>
@endsection
