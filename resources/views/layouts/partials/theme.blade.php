@if ($siteSettings->font_url)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $siteSettings->font_url }}" rel="stylesheet">
@endif

<style>
    :root {
        @foreach ($siteSettings->appearanceTokens() as $token => $value)
            {{ $token }}: {!! $value !!};
        @endforeach
    }
</style>
