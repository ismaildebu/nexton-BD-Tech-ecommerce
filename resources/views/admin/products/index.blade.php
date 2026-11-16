@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Products') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Name') }}</th><th class="p-2">{{ __('Price') }}</th><th class="p-2">{{ __('Status') }}</th></tr></thead>
        <tbody>
            @foreach ($products as $p)
                <tr class="border-t"><td class="p-2">{{ $p->name }}</td><td class="p-2">৳ {{ number_format($p->price, 2) }}</td><td class="p-2">{{ $p->status }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
