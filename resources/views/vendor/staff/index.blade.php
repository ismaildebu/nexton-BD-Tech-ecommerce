@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Staff') }}</h1>
        <form method="POST" action="{{ route('vendor.staff.store', $vendor) }}">
            @csrf
            <input type="email" name="email" placeholder="email" required class="border rounded px-2 py-1">
            <input type="text" name="staff_role" placeholder="role" required class="border rounded px-2 py-1">
            <button type="submit" class="bg-teal-700 text-white px-3 py-1 rounded">{{ __('Add') }}</button>
        </form>
        <ul class="mt-4">
            @foreach ($staff as $m)
                <li>{{ $m->user->name }} ({{ $m->staff_role }})</li>
            @endforeach
        </ul>
    </div>
@endsection