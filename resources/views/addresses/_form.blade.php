@php
    $address = $address ?? null;
@endphp

<datalist id="divisions">
    @foreach (['Barishal', 'Chattogram', 'Dhaka', 'Khulna', 'Mymensingh', 'Rajshahi', 'Rangpur', 'Sylhet'] as $division)
        <option value="{{ $division }}"></option>
    @endforeach
</datalist>

<div class="space-y-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.select name="type" :label="__('Address type')" required>
            @foreach (\App\Modules\Identity\Enums\AddressType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $address?->type?->value ?? 'home') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="full_name" :label="__('Full name')" :value="$address?->full_name" autocomplete="name" required />
    </div>

    <x-form.input name="phone" type="tel" :label="__('Mobile number')" :value="$address?->phone" autocomplete="tel"
        placeholder="01XXXXXXXXX" :hint="__('We will use this number for delivery updates.')" required />

    <x-form.textarea name="address_line" :label="__('Address')" autocomplete="street-address" rows="2" required
        :hint="__('House, road, village or area')">{{ $address?->address_line }}</x-form.textarea>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.input name="division" :label="__('Division')" :value="$address?->division" list="divisions" required />
        <x-form.input name="district" :label="__('District')" :value="$address?->district" required />
        <x-form.input name="upazila" :label="__('Upazila')" :value="$address?->upazila" />
        <x-form.input name="postal_code" :label="__('Postal code')" :value="$address?->postal_code" inputmode="numeric"
            pattern="[0-9]{4}" maxlength="4" placeholder="1000" :hint="__('Enter a 4-digit postal code.')" />
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
        <input type="hidden" name="is_default" value="0">
        <div class="flex items-start gap-3">
            <input id="is_default" type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default ?? false))
                aria-describedby="is_default-help{{ $errors->has('is_default') ? ' is_default-error' : '' }}"
                @error('is_default') aria-invalid="true" @enderror
                class="mt-1 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <div>
                <label for="is_default" class="text-sm font-semibold leading-5 text-slate-700">{{ __('Use as my default address') }}</label>
                <p id="is_default-help" class="ui-help">{{ __('We will preselect this address at checkout.') }}</p>
            </div>
        </div>
        @error('is_default')
            <p id="is_default-error" class="ui-error">{{ $message }}</p>
        @enderror
    </div>
</div>
