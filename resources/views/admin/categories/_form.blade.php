<x-ui.card :padding="false" class="max-w-3xl">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-6 p-5 sm:p-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="name" :label="__('Name')" :value="$category->name" maxlength="150" required />
            <x-form.input name="slug" :label="__('Slug')" :value="$category->slug" :hint="__('Use lowercase letters, numbers, and single hyphens.')" maxlength="160" required class="font-mono" />
        </div>

        <x-form.select name="parent_id" :label="__('Parent category (optional)')" :hint="__('Choose an active category or leave this empty for a top-level category.')">
            <option value="">{{ __('No parent') }}</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </x-form.select>

        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
            <input type="hidden" name="is_active" value="0">
            <div class="flex items-start gap-3">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) @error('is_active') aria-invalid="true" @enderror aria-describedby="is_active-help{{ $errors->has('is_active') ? ' is_active-error' : '' }}" class="mt-1 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <div>
                    <label for="is_active" class="text-sm font-semibold leading-5 text-slate-700">{{ __('Active (available for product selection)') }}</label>
                    <p id="is_active-help" class="ui-help">{{ __('Inactive categories remain in the catalog but cannot be selected for products.') }}</p>
                </div>
            </div>
            @error('is_active')
                <p id="is_active-error" class="ui-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
            <x-ui.button :href="route('admin.categories.index')" variant="outline">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-9"/></svg>
                {{ $category->exists ? __('Save category') : __('Create category') }}
            </x-ui.button>
        </div>
    </form>
</x-ui.card>
