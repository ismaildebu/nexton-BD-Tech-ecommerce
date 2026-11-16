@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Customers') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Name') }}</th><th class="p-2">{{ __('Email') }}</th></tr></thead>
        <tbody>
            @foreach ($customers as $c)
                <tr class="border-t"><td class="p-2">{{ $c->name }}</td><td class="p-2">{{ $c->email }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection