@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Payments') }}</h1>
    @if ($payments->isEmpty())
        <p class="text-gray-500">{{ __('No payments yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($payments as $payment)
                <li class="border rounded p-3 bg-white">৳ {{ number_format($payment->amount, 2) }} ({{ $payment->status }})</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
@endsection