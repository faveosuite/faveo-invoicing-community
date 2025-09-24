<!DOCTYPE html>
<html lang="en">

<?php
    $set = new \App\Model\Common\Setting();
    $set = $set->findOrFail(1);
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($set->fav_icon)
        <link rel="shortcut icon" href='{{ $set->fav_icon }}' type="image/x-icon" />
    @endif

    {{-- CDN: open a connection early so asset requests don't pay DNS + TCP cost --}}
    @if(config('theme.use_cdn') && config('theme.cdn_host'))
        <link rel="preconnect" href="{{ config('theme.cdn_host') }}">
        <link rel="dns-prefetch" href="{{ config('theme.cdn_host') }}">
    @endif

    {{-- ── CRITICAL CSS (render-blocking — needed before first paint) ────────────
         Layout shell, icons and fonts must be present when the browser paints.
         Keep this list small: only what the visible shell (navbar/sidebar) uses.  --}}
    <link rel="stylesheet" href="{{ themeAsset('bootstrap-icons-css') }}">
    <link rel="stylesheet" href="{{ themeAsset('adminlte-css') }}">
    <link rel="stylesheet" href="{{ themeAsset('fontawesome-css') }}">
    <link rel="stylesheet" href="{{ themeAsset('flag-icons-css') }}">

    {{-- ── NON-CRITICAL CSS (async — zero render-blocking) ─────────────────────
         media="print" tricks the browser into downloading without blocking paint.
         onload switches it to all media once the file is ready.
         These are only used on dashboard / reports pages.                         --}}
    <link rel="stylesheet" href="{{ themeAsset('overlayscrollbars-css') }}"
          media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ themeAsset('apexcharts-css') }}"
          media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ themeAsset('jsvectormap-css') }}"
          media="print" onload="this.media='all'">

    {{-- Fallback for browsers with JS disabled --}}
    <noscript>
        <link rel="stylesheet" href="{{ themeAsset('overlayscrollbars-css') }}">
        <link rel="stylesheet" href="{{ themeAsset('apexcharts-css') }}">
        <link rel="stylesheet" href="{{ themeAsset('jsvectormap-css') }}">
    </noscript>

    {{-- ── Translations exposed to Vue ──────────────────────────────────────────
         Loads all lang/*.php files for the current locale into window.translator.
         Cached by the browser for 30 days; busted automatically on locale change. --}}
    <script type="text/javascript" src="{{ url('js/lang') }}"></script>

    {{-- ── Vue app (type="module" → always deferred, never render-blocking) ─────
         Vite also injects its own CSS <link> here at build time.                  --}}
    @vite(['resources/assets/js/main.js'])
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary fs-7">

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

        {{-- Shown until Vue mounts — uses critical CSS already loaded above --}}
        <div style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:9999">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading…</span>
            </div>
        </div>
    </div>

    {{-- ── LAYOUT JS (end of body, no defer) ────────────────────────────────────
         Must execute BEFORE Vue initialises — strict dependency order:
         popper → bootstrap → overlayscrollbars → adminlte.
         Being at end of body means they don't block rendering, but they do
         execute before the deferred Vue module script.                            --}}
    <script src="{{ themeAsset('popper-js') }}"></script>
    <script src="{{ themeAsset('bootstrap-js') }}"></script>
    <script src="{{ themeAsset('overlayscrollbars-js') }}"></script>
    <script src="{{ themeAsset('adminlte-js') }}"></script>

    {{-- ── PAGE-SPECIFIC HEAVY JS (defer) ───────────────────────────────────────
         apexcharts (~1 MB) and jsvectormap + world.js are only used on the
         dashboard / reports pages. defer means the browser downloads them in
         parallel with HTML parsing but executes only after parsing is done.
         Vue components that use these libs should guard with window.ApexCharts
         or import the npm package directly instead (long-term goal).              --}}
    <script src="{{ themeAsset('sortable-js') }}" defer></script>
    <script src="{{ themeAsset('apexcharts-js') }}" defer></script>
    <script src="{{ themeAsset('jsvectormap-js') }}" defer></script>
    <script src="{{ themeAsset('jsvectormap-world') }}" defer></script>

</body>
</html>
