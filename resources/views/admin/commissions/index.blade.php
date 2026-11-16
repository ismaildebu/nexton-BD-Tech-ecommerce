@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Commissions') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Amount') }}</th><th class="p-2">{{ __('Rate') }}</th></tr></thead>
        <tbody>
            @foreach ($commissions as $c)
                <tr class="border-t"><td class="p-2">৳ {{ number_format($c->amount, 2) }}</td><td class="p-2">{{ $c->rate }}%</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $commissions->links() }}</div>
@endsection
