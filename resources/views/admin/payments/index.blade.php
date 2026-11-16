@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Payments') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Amount') }}</th><th class="p-2">{{ __('Status') }}</th></tr></thead>
        <tbody>
            @foreach ($payments as $p)
                <tr class="border-t"><td class="p-2">৳ {{ number_format($p->amount, 2) }}</td><td class="p-2">{{ $p->status }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
