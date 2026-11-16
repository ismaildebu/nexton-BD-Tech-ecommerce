@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Bank Accounts') }}</h1>
        <form method="POST" action="{{ route('vendor.bank-accounts.store', $vendor) }}">
            @csrf
            <input type="text" name="account_holder_name" placeholder="holder" required class="border rounded px-2 py-1">
            <input type="text" name="bank_name" placeholder="bank" required class="border rounded px-2 py-1">
            <input type="text" name="account_number" placeholder="account number" required class="border rounded px-2 py-1">
            <button type="submit" class="bg-teal-700 text-white px-3 py-1 rounded">{{ __('Add') }}</button>
        </form>
        <ul class="mt-4">
            @foreach ($accounts as $acc)
                <li>{{ $acc->bank_name }} - {{ $acc->masked_account_number }}</li>
            @endforeach
        </ul>
    </div>
@endsection