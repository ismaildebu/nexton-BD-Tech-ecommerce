@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('Documents') }}</h1>
        <form method="POST" action="{{ route('vendor.documents.store', $vendor) }}" enctype="multipart/form-data">
            @csrf
            <input type="text" name="document_type" placeholder="type" required class="border rounded px-2 py-1">
            <input type="file" name="file" required>
            <button type="submit" class="bg-teal-700 text-white px-3 py-1 rounded">{{ __('Upload') }}</button>
        </form>
        <ul class="mt-4">
            @foreach ($documents as $doc)
                <li>{{ $doc->original_filename }} ({{ $doc->mime_type }})</li>
            @endforeach
        </ul>
    </div>
@endsection