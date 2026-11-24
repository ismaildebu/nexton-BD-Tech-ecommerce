@extends('layouts.vendor')
@section('title', __('Documents'))
@section('content')
    <x-ui.page-header
        :title="__('Documents')"
        :description="__('Upload business documents for review and track their verification status.')"
        :eyebrow="__('Business profile')"
    />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m-5 5 5-5 5 5M4 15v5h16v-5"/></svg>
                </span>
                <div>
                    <h2 class="ui-section-title">{{ __('Upload a document') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Add a clear copy of the document you want the admin team to review.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('vendor.documents.store', $vendor) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <x-form.input
                    name="document_type"
                    :label="__('Document type')"
                    :value="old('document_type')"
                    placeholder="{{ __('e.g. national_id') }}"
                    maxlength="64"
                    required
                />
                <div>
                    <label for="file" class="ui-label">{{ __('Document file') }} <span class="text-brand-600" aria-hidden="true">*</span></label>
                    <input
                        id="file"
                        type="file"
                        name="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                        @if ($errors->has('file')) aria-invalid="true" aria-describedby="file-help file-error" @else aria-describedby="file-help" @endif
                        class="block w-full rounded-xl border border-slate-200 bg-white p-2 text-sm text-slate-600 shadow-sm transition file:mr-4 file:min-h-11 file:cursor-pointer file:rounded-lg file:border-0 file:bg-ink-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-ink-800 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                    >
                    <p id="file-help" class="ui-help">{{ __('PDF, JPG, or PNG. Maximum file size: 5 MB.') }}</p>
                    @error('file')
                        <p id="file-error" class="ui-error">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.button type="submit" variant="primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m-5 5 5-5 5 5M4 15v5h16v-5"/></svg>
                    {{ __('Upload') }}
                </x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-50 text-accent-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 3l9 4v5c0 5-3.5 8-9 9-5.5-1-9-4-9-9V7l9-4z"/></svg>
            </span>
            <h2 class="mt-4 ui-section-title">{{ __('Verification process') }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Uploaded documents are reviewed by an administrator. Their status will appear alongside each file.') }}</p>
            <div class="mt-5 flex flex-wrap gap-2">
                <x-ui.status status="pending" />
                <x-ui.status status="approved" />
                <x-ui.status status="rejected" />
            </div>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mt-8">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
            <h2 class="ui-section-title">{{ __('Uploaded documents') }}</h2>
            <span class="text-sm font-semibold tabular-nums text-slate-500">{{ number_format($documents->count()) }}</span>
        </div>
        @if ($documents->isEmpty())
            <x-ui.empty-state
                :title="__('No documents uploaded yet.')"
                :description="__('Use the form above to add your first business document.')"
            />
        @else
            <x-ui.table :caption="__('Uploaded business documents and verification status')" class="rounded-none border-0 shadow-none [&_table]:min-w-[48rem]">
                <x-slot:head>
                    <tr>
                        <th scope="col">{{ __('Document') }}</th>
                        <th scope="col">{{ __('Type') }}</th>
                        <th scope="col">{{ __('File size') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Uploaded') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($documents as $doc)
                    <tr>
                        <td class="max-w-80">
                            <p class="break-all font-semibold text-slate-900">{{ $doc->original_filename }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $doc->mime_type }}</p>
                            @if ($doc->rejection_reason)
                                <p class="mt-2 text-xs font-medium text-rose-600">{{ $doc->rejection_reason }}</p>
                            @endif
                        </td>
                        <td>{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</td>
                        <td class="whitespace-nowrap tabular-nums">{{ $doc->file_size ? number_format($doc->file_size).' '.__('bytes') : '—' }}</td>
                        <td><x-ui.status :status="$doc->verification_status ?: 'pending'" /></td>
                        <td class="whitespace-nowrap">
                            @if ($doc->created_at)
                                <time datetime="{{ $doc->created_at->toIso8601String() }}">{{ $doc->created_at->format('M j, Y') }}</time>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
