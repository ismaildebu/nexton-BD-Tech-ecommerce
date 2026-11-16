@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Coupons') }}</h1>
    @if ($coupons->isEmpty())
        <p class="text-gray-500">{{ __('No coupons yet.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($coupons as $c)
                <li class="border rounded p-3 bg-white">{{ $c->code }} - {{ $c->value }}</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $coupons->links() }}</div>
    @endif
@endsection