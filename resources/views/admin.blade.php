<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<?php $set = \App\Model\Common\Setting::findOrFail(1); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $set->favicon_title }}</title>

    @if($set->fav_icon)
        <link rel="shortcut icon" href="{{ $set->fav_icon }}" type="image/x-icon">
    @endif

    {{-- Critical CSS — render-blocking, needed before first paint --}}
    <link rel="stylesheet" href="{{ assetLink('css', 'bootstrap') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'fontawesome') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'AdminLTE') }}">

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
    @vite(['resources/assets/js/main.js'])
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
         data-locale="{{ app()->getLocale() }}"
         data-app-version="{{ config('app.version', '') }}"
         data-page-title="{{ $set->favicon_title }}"
         data-app-title="{{ $set->title }}"
         data-app-logo="{{ $set->admin_logo }}"
         data-website="{{ $set->website }}"
         data-company="{{ $set->company }}">

        {{-- Loading spinner shown until Vue mounts --}}
        <div style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:9999">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
        </div>
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
