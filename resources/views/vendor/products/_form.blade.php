<div class="space-y-8">
    <fieldset class="space-y-5">
        <legend class="ui-section-title">{{ __('Product details') }}</legend>
        <x-form.input
            name="name"
            :label="__('Name')"
            :value="$product->name ?? ''"
            maxlength="255"
            required
        />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input
                name="price"
                :label="__('Price (BDT)')"
                type="number"
                step="0.01"
                min="0.01"
                :value="$product->price ?? ''"
                required
            />
            <x-form.select name="category_id" :label="__('Category')">
                <option value="">{{ __('— None —') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-form.select>
        </div>
        @if ($brands->isNotEmpty())
            <x-form.select name="brand_id" :label="__('Brand')">
                <option value="">{{ __('— None —') }}</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </x-form.select>
        @endif
    </fieldset>

    <fieldset class="border-t border-slate-100 pt-7">
        <legend class="ui-section-title">{{ __('Description') }}</legend>
        <x-form.textarea name="description" :label="__('Description')" rows="5" maxlength="5000" :hint="__('Describe the product features, materials, and anything customers should know.')">{{ $product->description ?? '' }}</x-form.textarea>
    </fieldset>
</div>
