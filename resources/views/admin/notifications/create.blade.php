@extends('layouts.admin')
@section('title', __('Send notification'))
@section('content')
    <div class="ui-page-narrow">
        <x-ui.page-header :title="__('Send notification')" :description="__('Compose a targeted message for a customer’s notification center.')" :eyebrow="__('Customer communication')">
            <x-slot:action>
                <x-ui.button :href="route('admin.notifications.index')" variant="outline">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
                    {{ __('Back to notifications') }}
                </x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <x-ui.card :padding="false">
            <div class="flex items-center gap-3 border-b border-slate-100 p-5 sm:p-6">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-600" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L6 12zm0 0h7.5"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Send a notification to a customer') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Choose the recipient, message type, and content.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-6 p-5 sm:p-6">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.select name="user_id" :label="__('Recipient')" required>
                        <option value="">{{ __('Select a user') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }}@if ($user->email) — {{ $user->email }}@endif
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.select name="type" :label="__('Type')" required>
                        @foreach (['info' => __('Information'), 'success' => __('Success'), 'warning' => __('Warning')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'info') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <x-form.textarea id="message" name="data[message]" :label="__('Message')" :hint="__('Keep the message clear and concise. Up to 2,000 characters.')" rows="5" maxlength="2000" required>{{ old('data.message') }}</x-form.textarea>

                <x-form.input id="url" name="data[url]" :label="__('Optional link URL')" :hint="__('Add a full URL, including https://, to link the notification to a storefront page.')" type="url" maxlength="2048" autocomplete="url" />

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <x-ui.button :href="route('admin.notifications.index')" variant="outline">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="secondary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L6 12zm0 0h7.5"/></svg>
                        {{ __('Send notification') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
