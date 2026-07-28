<?php
$set = \App\Model\Common\Setting::findOrFail(1);
$rtl = isRtlForLang();
$seoMeta = app(\App\Services\Seo\SeoMetaService::class);

// This request's own path — same cascade admin.blade.php always used
// (General SEO -> per-route default -> hardcoded literal).
$adminPath = (string) collect(explode('/', trim(request()->path(), '/')))->skip(1)->implode('/');
$seo = $seoMeta->resolveAdminMeta($adminPath);

// Every other admin route, pre-resolved through the exact same cascade —
// shipped down so adminRouter.js/useBreadcrumb.js never re-implement it,
// and never need a hand-typed title/titleKey per route.
$adminRoutesSeo = $seoMeta->resolveAdminRoutes();
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $set->company }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    @if(!empty($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
    @endif

    <meta name="twitter:card" content="{{ !empty($seo['image']) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] }}">
    @if(!empty($seo['image']))
    <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif

    @if($set->fav_icon)
        <link rel="shortcut icon" href="{{ $set->fav_icon }}" type="image/x-icon">
    @endif

    {{-- Critical CSS — render-blocking, needed before first paint --}}
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'bootstrap-rtl' : 'bootstrap') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'fontawesome') }}">
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'AdminLTE-rtl' : 'AdminLTE') }}">

    {{-- Non-critical CSS — async, zero render-blocking --}}
    <link rel="stylesheet" href="{{ assetLink('css', 'overlayscrollbars') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ assetLink('css', 'apexcharts') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ assetLink('css', 'jsvectormap') }}" media="print" onload="this.media='all'">

    <noscript>
        <link rel="stylesheet" href="{{ assetLink('css', 'overlayscrollbars') }}">
        <link rel="stylesheet" href="{{ assetLink('css', 'apexcharts') }}">
        <link rel="stylesheet" href="{{ assetLink('css', 'jsvectormap') }}">
    </noscript>

    <script src="{{ url('js/lang') }}"></script>

    {{-- Vue app — type="module" defers automatically; Vite injects CSS link at build time --}}
    @vite(['resources/assets/js/admin.js'])
</head>

<body class="layout-fixed fixed-header bg-body-tertiary sidebar-expand-lg sidebar-mini app-loaded fs-7">

    <div class="app-wrapper" id="app-root"
         data-theme="{{ config('theme.active') }}"
         data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
         data-base-url="{{ url('/') }}"
         data-admin-url="{{ url('/admin') }}"
         data-asset-url="{{ asset('') }}"
         data-user-name="{{ auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'Admin' }}"
         data-user-email="{{ auth()->user()?->email ?? '' }}"
         data-user-avatar="{{ auth()->user()?->profile_pic ?? '' }}"
         data-user-timezone="{{ auth()->user()?->timezone?->name ?? '' }}"
         data-locale="{{ app()->getLocale() }}"
         data-app-version="{{ config('app.version', '') }}"
         data-sentry-dsn="{{ config('sentry.dsn') }}"
         data-sentry-enabled="{{ config('app.sentry_reporting') ? 'true' : 'false' }}"
         data-app-title="{{ $set->title }}"
         data-app-logo="{{ $set->admin_logo }}"
         data-website="{{ $set->website }}"
         data-company="{{ $set->company }}"
         data-admin-routes="{{ json_encode($adminRoutesSeo) }}">

    </div>

    {{-- Layout JS — strict load order: bootstrap (includes popper) → overlayscrollbars → adminlte --}}
    <script src="{{ assetLink('js', 'bootstrap') }}"></script>
    <script src="{{ assetLink('js', 'overlayscrollbars') }}"></script>
    <script src="{{ assetLink('js', 'AdminLTE') }}"></script>

    {{-- Page-specific JS — deferred, only used on dashboard / reports pages --}}
    <script src="{{ assetLink('js', 'sortablejs') }}" defer></script>
    <script src="{{ assetLink('js', 'apexcharts') }}" defer></script>
    <script src="{{ assetLink('js', 'jsvectormap') }}" defer></script>
    <script src="{{ assetLink('js', 'jsvectormap-world') }}" defer></script>

</body>
</html>
