@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Reviews') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Rating') }}</th><th class="p-2">{{ __('Comment') }}</th></tr></thead>
        <tbody>
            @foreach ($reviews as $r)
                <tr class="border-t"><td class="p-2">{{ $r->rating }}/5</td><td class="p-2">{{ $r->comment }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $reviews->links() }}</div>
@endsection
