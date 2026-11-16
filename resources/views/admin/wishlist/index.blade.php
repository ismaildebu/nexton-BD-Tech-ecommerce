@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Wishlist') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('User') }}</th><th class="p-2">{{ __('Product') }}</th></tr></thead>
        <tbody>
            @foreach ($items as $w)
                <tr class="border-t"><td class="p-2">{{ $w->user->name ?? '-' }}</td><td class="p-2">{{ $w->product->name ?? '-' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
