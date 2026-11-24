@extends('layouts.frontend')
@section('title', __(':page - :store', ['page' => $page->title, 'store' => $siteSettings->site_name]))

@section('content')
<div class="ui-page-narrow">
    <x-ui.page-header :title="$page->title" />

    <x-ui.card :padding="false">
        <article class="ui-prose min-w-0 overflow-x-auto break-words p-5 sm:p-8 lg:p-10">
            {!! $page->content !!}
        </article>
    </x-ui.card>
</div>
@endsection
