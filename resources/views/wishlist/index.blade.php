@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Wishlist') }}</h1>
    @if ($items->isEmpty())
        <p class="text-gray-500">{{ __('Your wishlist is empty.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($items as $w)
                <li class="border rounded p-3 bg-white">{{ $w->product->name ?? '-' }}</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
@endsection