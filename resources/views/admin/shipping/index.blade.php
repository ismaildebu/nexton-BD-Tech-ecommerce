@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Shipping') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Carrier') }}</th><th class="p-2">{{ __('Tracking') }}</th><th class="p-2">{{ __('Status') }}</th></tr></thead>
        <tbody>
            @foreach ($shippings as $s)
                <tr class="border-t"><td class="p-2">{{ $s->carrier }}</td><td class="p-2">{{ $s->tracking_number }}</td><td class="p-2">{{ $s->status }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $shippings->links() }}</div>
@endsection
