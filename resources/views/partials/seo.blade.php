@php
    $seo = app(\App\Support\Seo::class);
    $site = \App\Models\Setting::publicPayload();
    $locale = app()->getLocale();
    $alternates = $seo->resolvedAlternates();
    $description = $seo->resolvedDescription();
    $image = $seo->resolvedImage();
    $canonical = $seo->resolvedCanonical();

    $organisation = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'SportsOrganization',
        'name' => $site['name'] ?? config('app.name'),
        'url' => url('/'),
        'logo' => $site['logo'] ? url($site['logo']) : null,
        'email' => $site['email'] ?? null,
        'telephone' => $site['phone'] ?? null,
        'address' => ($site['address'] ?? null)
            ? ['@type' => 'PostalAddress', 'addressLocality' => $site['address']]
            : null,
        'sameAs' => collect($site['socials'] ?? [])->pluck('url')->filter()->values()->all() ?: null,
    ]);
@endphp

<title inertia>{{ $seo->resolvedTitle() }}</title>

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

<link rel="canonical" href="{{ $canonical }}">

@if ($seo->noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
@endif

@foreach ($alternates as $code => $url)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $url }}">
@endforeach
@if ($alternates)
    <link rel="alternate" hreflang="x-default" href="{{ $alternates[\App\Support\Locales::default()] ?? $canonical }}">
@endif

<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:site_name" content="{{ $site['name'] ?? config('app.name') }}">
<meta property="og:title" content="{{ $seo->resolvedTitle() }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ $locale }}">
@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif
@if ($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $seo->resolvedTitle() }}">
@endif

@foreach ($seo->properties as $name => $value)
    <meta property="{{ $name }}" content="{{ $value }}">
@endforeach
@foreach ($alternates as $code => $alternateUrl)
@if ($code !== $locale)
    <meta property="og:locale:alternate" content="{{ $code }}">
@endif
@endforeach

<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo->resolvedTitle() }}">
@if ($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif

<script type="application/ld+json">{!! json_encode($organisation, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

@foreach ($seo->schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
