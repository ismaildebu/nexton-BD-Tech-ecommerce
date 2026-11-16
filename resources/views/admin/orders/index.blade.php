@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Orders') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Number') }}</th><th class="p-2">{{ __('Total') }}</th><th class="p-2">{{ __('Status') }}</th></tr></thead>
        <tbody>
            @foreach ($orders as $o)
                <tr class="border-t"><td class="p-2">{{ $o->order_number }}</td><td class="p-2">৳ {{ number_format($o->total, 2) }}</td><td class="p-2">{{ $o->status }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
