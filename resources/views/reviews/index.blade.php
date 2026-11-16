@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Reviews') }}</h1>
    @if ($reviews->isEmpty())
        <p class="text-gray-500">{{ __('No reviews yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($reviews as $r)
                <li class="border rounded p-3 bg-white">{{ $r->rating }}/5 - {{ $r->comment }}</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
@endsection