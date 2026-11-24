@if (session('status'))
    <x-ui.alert tone="success" class="mb-5" role="status">{{ session('status') }}</x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert tone="error" class="mb-5">{{ session('error') }}</x-ui.alert>
@endif

@if ($errors->any())
    <x-ui.alert tone="error" class="mb-5" :title="__('Please correct the following errors:')">
        <ul class="mt-1 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
