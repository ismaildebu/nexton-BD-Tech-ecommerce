@extends('layouts.frontend')
@section('content')
    <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>
    <p class="text-gray-600 mb-2">৳ {{ number_format($product->price, 2) }}</p>
    <p class="text-gray-700">{{ $product->description }}</p>
@endsection