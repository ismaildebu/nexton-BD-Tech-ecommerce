@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Commissions') }}</h1>
    @if ($commissions->isEmpty())
        <p class="text-gray-500">{{ __('No commissions yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($commissions as $c)
                <li class="border rounded p-3 bg-white">৳ {{ number_format($c->amount, 2) }} ({{ $c->rate }}%)</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $commissions->links() }}</div>
    @endif
@endsection