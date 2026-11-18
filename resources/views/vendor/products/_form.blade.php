@php $field = 'w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20'; @endphp

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">{{ __('Name') }}</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name ?? '') }}" class="{{ $field }}" required>
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="price" class="mb-1 block text-sm font-medium">{{ __('Price (BDT)') }}</label>
        <input id="price" name="price" type="number" step="0.01" min="0.01" value="{{ old('price', $product->price ?? '') }}" class="{{ $field }}" required>
        @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category_id" class="mb-1 block text-sm font-medium">{{ __('Category') }}</label>
        <select id="category_id" name="category_id" class="{{ $field }}">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    @if ($brands->isNotEmpty())
        <div>
            <label for="brand_id" class="mb-1 block text-sm font-medium">{{ __('Brand') }}</label>
            <select id="brand_id" name="brand_id" class="{{ $field }}">
                <option value="">{{ __('— None —') }}</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brand_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="5" class="{{ $field }}">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>