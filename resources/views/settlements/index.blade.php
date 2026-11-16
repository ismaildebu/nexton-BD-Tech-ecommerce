@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Settlements') }}</h1>
    @if ($settlements->isEmpty())
        <p class="text-gray-500">{{ __('No settlements yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($settlements as $s)
                <li class="border rounded p-3 bg-white">৳ {{ number_format($s->amount, 2) }} ({{ $s->status }})</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $settlements->links() }}</div>
    @endif
@endsection