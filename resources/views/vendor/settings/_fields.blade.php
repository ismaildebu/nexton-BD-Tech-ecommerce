@php
    $value = fn (string $name, mixed $default = null) => old($name, $vendor->$name ?? $default);
@endphp

<div class="space-y-8">
    <fieldset class="space-y-5">
        <legend class="ui-section-title">{{ __('Business details') }}</legend>
        <x-form.input name="business_name" :label="__('Business name')" :value="$value('business_name')" maxlength="255" required />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input
                name="business_type"
                :label="__('Business type')"
                :value="$value('business_type')"
                maxlength="100"
                :placeholder="__('Company, individual, partnership')"
            />
            <x-form.input name="business_email" :label="__('Business email')" type="email" :value="$value('business_email')" maxlength="255" />
            <x-form.input name="business_phone" :label="__('Business phone')" :value="$value('business_phone')" maxlength="32" />
            <x-form.input name="contact_person" :label="__('Contact person')" :value="$value('contact_person')" maxlength="150" />
        </div>
    </fieldset>

    <fieldset class="space-y-5 border-t border-slate-100 pt-7">
        <legend class="ui-section-title">{{ __('Business location') }}</legend>
        <x-form.textarea name="business_address" :label="__('Business address')" rows="3" maxlength="2000">{{ $value('business_address') }}</x-form.textarea>
        <div class="grid gap-5 sm:grid-cols-3">
            <x-form.input name="district" :label="__('District')" :value="$value('district')" maxlength="100" />
            <x-form.input name="upazila" :label="__('Upazila')" :value="$value('upazila')" maxlength="100" />
            <x-form.input name="postal_code" :label="__('Postal code')" :value="$value('postal_code')" maxlength="10" />
        </div>
    </fieldset>

    <fieldset class="border-t border-slate-100 pt-7">
        <legend class="ui-section-title">{{ __('About the business') }}</legend>
        <x-form.textarea
            name="business_description"
            :label="__('Business description')"
            rows="4"
            maxlength="5000"
            :hint="__('Tell customers what your business sells and what makes it different.')"
        >{{ $value('business_description') }}</x-form.textarea>
    </fieldset>
</div>
