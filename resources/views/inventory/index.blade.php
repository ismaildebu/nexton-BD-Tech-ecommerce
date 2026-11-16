@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Inventory') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Product') }}</th><th class="p-2">{{ __('Qty') }}</th></tr></thead>
        <tbody>
            @foreach ($items as $item)
                <tr class="border-t"><td class="p-2">{{ $item->product->name ?? '-' }}</td><td class="p-2 text-center">{{ $item->quantity }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection