@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Notifications') }}</h1>
    <table class="w-full text-sm bg-white rounded shadow">
        <thead><tr><th class="p-2 text-left">{{ __('Type') }}</th></tr></thead>
        <tbody>
            @foreach ($notifications as $n)
                <tr class="border-t"><td class="p-2">{{ $n->type }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
