@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ $page->title }}</h1>
    <div class="prose max-w-none">{!! $page->content !!}</div>
@endsection