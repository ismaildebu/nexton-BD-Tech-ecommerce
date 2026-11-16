@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Subscriptions') }}</h1>
    @if ($subscriptions->isEmpty())
        <p class="text-gray-500">{{ __('No subscriptions yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($subscriptions as $s)
                <li class="border rounded p-3 bg-white">{{ $s->plan }} ({{ $s->status }})</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $subscriptions->links() }}</div>
    @endif
@endsection