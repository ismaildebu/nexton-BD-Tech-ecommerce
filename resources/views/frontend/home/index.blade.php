@extends('layouts.frontend')
@section('title', __(':store - Curated Tech & Lifestyle Marketplace', ['store' => $siteSettings->site_name]))

@section('content')
    @php($homepageDesign = $siteSettings->homepageDesign())
    @php($homepageSections = $siteSettings->homepageSections())

    <div class="ui-page" data-homepage-design="{{ $homepageDesign }}">
        <h1 class="sr-only">{{ __(':store curated tech and lifestyle marketplace', ['store' => $siteSettings->site_name]) }}</h1>

        @foreach ($homepageSections as $key => $section)
            @if ($section['enabled'])
                @include('frontend.home.sections.'.$key, ['section' => $section])
            @endif
        @endforeach
    </div>
@endsection
