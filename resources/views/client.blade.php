<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<?php $set = \App\Model\Common\Setting::findOrFail(1); ?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $set->favicon_title_client }}</title>

    @if($set->fav_icon)
        <link rel="shortcut icon" href="{{ $set->fav_icon }}" type="image/x-icon">
    @endif

    {{-- Google Fonts --}}
    <link id="googleFonts" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800%7CShadows+Into+Light&display=swap" rel="stylesheet">

    {{-- Porto vendor CSS --}}
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-animate') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-simple-icons') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-owl') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-owl-theme') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-magnific') }}">

    {{-- Reuse common vendors (same as admin panel) --}}
    <link rel="stylesheet" href="{{ assetLink('css', 'bootstrap') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'fontawesome') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-theme') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-elements') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-custom') }}">

    {{-- Porto modernizr (must be in <head>) --}}
    <script src="{{ assetLink('js', 'porto-modernizr') }}"></script>

    <script src="{{ url('js/lang') }}"></script>

    {{-- Vue client SPA — Vite injects its CSS at build time --}}
    @vite(['resources/assets/js/client.js'])
</head>

<body data-plugin-scroll-offset="85">

    <div id="app-client"
         data-theme="porto"
         data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
         data-base-url="{{ url('/') }}"
         data-client-url="{{ url('/client') }}"
         data-asset-url="{{ asset('') }}"
         data-locale="{{ app()->getLocale() }}"
         data-locale-rtl="{{ in_array(app()->getLocale(), ['ar', 'he']) ? 'true' : 'false' }}"
         data-page-title="{{ $set->favicon_title_client }}"
         data-app-logo="{{ $set->logo }}"
         data-company="{{ $set->company }}"
         data-website="{{ $set->website }}"
         data-user-name="{{ auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : '' }}"
         data-user-email="{{ auth()->user()?->email ?? '' }}"
         data-user-avatar="{{ auth()->user()?->profile_pic ?? '' }}">
    </div>

    {{-- jQuery (required by Porto theme.js) --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    {{-- Bootstrap 4 JS (Porto uses BS4) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    {{-- Porto core JS --}}
    <script src="{{ assetLink('js', 'porto-theme') }}"></script>
    <script src="{{ assetLink('js', 'porto-theme-init') }}"></script>

</body>
</html>
