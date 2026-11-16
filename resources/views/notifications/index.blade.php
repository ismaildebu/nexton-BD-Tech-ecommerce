@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ __('Notifications') }}</h1>
    @if ($notifications->isEmpty())
        <p class="text-gray-500">{{ __('No notifications.') }}</p>
    @else
        <ul class="space-y-2">
            @foreach ($notifications as $n)
                <li class="border rounded p-3 bg-white">{{ $n->type }}</li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
@endsection