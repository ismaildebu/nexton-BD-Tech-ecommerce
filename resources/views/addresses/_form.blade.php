@php
    $address = $address ?? null;
@endphp

<datalist id="divisions">
    @foreach (['Barishal', 'Chattogram', 'Dhaka', 'Khulna', 'Mymensingh', 'Rajshahi', 'Rangpur', 'Sylhet'] as $division)
        <option value="{{ $division }}"></option>
    @endforeach
</datalist>

<div>
    <label for="type" class="block text-sm font-medium text-gray-800">{{ __('Address type') }}</label>
    <select id="type" name="type" required
        class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-700/30">
        @foreach (\App\Modules\Identity\Enums\AddressType::cases() as $type)
            <option value="{{ $type->value }}" @selected(old('type', $address?->type?->value ?? 'home') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    @error('type')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<x-form.input name="full_name" :label="__('Full name')" :value="$address?->full_name" autocomplete="name" required />
<x-form.input name="phone" type="tel" :label="__('Mobile number')" :value="$address?->phone" autocomplete="tel" required />
<x-form.input name="address_line" :label="__('Address')" :value="$address?->address_line" autocomplete="street-address" required
    :hint="__('House, road, village or area')" />

<div class="grid gap-4 sm:grid-cols-2">
    <x-form.input name="division" :label="__('Division')" :value="$address?->division" list="divisions" required />
    <x-form.input name="district" :label="__('District')" :value="$address?->district" required />
    <x-form.input name="upazila" :label="__('Upazila')" :value="$address?->upazila" />
    <x-form.input name="postal_code" :label="__('Postal code')" :value="$address?->postal_code" inputmode="numeric" maxlength="4" />
</div>

<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="is_default" value="0">
    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default ?? false))
        class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
    {{ __('Use as my default address') }}
</label>
