<!DOCTYPE html>

<html>
<?php

use App\Model\Common\Setting;

$versioning = config('app.version');
$icon = Setting::value('admin_logo');
$authUser = Auth::user();
$userData = [
    'client_id' => $authUser->id,
    'client_fname' => $authUser->first_name,
    'client_lname' => $authUser->last_name,
    'client_email' => $authUser->email,
    'client_profile_pic' => $authUser->profile_pic,
    'client_mobile_code' => $authUser->mobile_code,
    'client_iso2' => $authUser->mobile_country_iso,
    'client_timezone_id' => $authUser->timezone_id,
];
?>
<head>

    <meta charset="UTF-8">

    <base href="{{ url('/') }}">

    <title> License Manager </title>

    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <meta name="_token" content="{!! csrf_token() !!}"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="api-base-url" content="{{ url('/') }}"/>
    <meta name="app-base-path" content="{{ parse_url(url('/'), PHP_URL_PATH) ?: '/' }}"/>

    <link href="{{ $icon }}" rel="shortcut icon">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <link href="{{ assetLink('css','adminlte-4') }}" rel='stylesheet' type='text/css'/>

    <link href="{{ assetLink('css','font-awesome-7') }}" rel="stylesheet" type="text/css"/>

    <link href="{{ assetLink('css','ionicons') }}" rel="stylesheet" type="text/css"/>

    <link href="{{ assetLink('css','select2') }}" rel="stylesheet" type="text/css" media="none"
          onload="this.media='all';"/>

    <link rel="stylesheet" href="{{ assetLink('css','new-overlay') }}">

    <link rel="stylesheet" href="{{ assetLink('css','glyphicon') }}">

    <link rel="stylesheet" href="{{ assetLink('css','icheck') }}">

    <script src="{{ assetLink('js','jquery') }}" type="text/javascript" media="none" onload="this.media='all';">

    </script>

    <script src="{{ assetLink('js','polyfill') }}"></script>
    <script src="{{ assetLink('js','select2') }}" type="text/javascript"></script>

    <style>

        .VuePagination__pagination {
            margin-top: -5px !important;
            margin-right: -15px !important;
            float: right !important;
        }

        .VuePagination {
            margin-top: 10px !important;
        }

        .VuePagination__count {
            display: contents !important;
            margin-top: -10px !important;
        }

        .VuePagination .text-center {
            text-align: left !important;
            width: inherit;
        }

        .VueTables__search {
            float: right;
        }

        .VueTables__limit {
            float: left !important;
        }

        .VueTables__search-field input {
            width: 300px !important;
        }

        .form-group.has-error label {
            color: #dd4b39;
        }

        .form-group.has-error .vs__dropdown-toggle {
            border-color: #d73925 !important;
        }

        a {
            text-decoration: none !important;
        }

        body {
            margin: 0;
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol" !important;
            font-size: 1rem !important;
            font-weight: 400 !important;
            line-height: 1.5 !important;
            color: #212529 !important;
            text-align: left !important;
            background-color: #fff !important;
        }

        .iti {
            width: 100%;
            --iti-path-flags-1x: url({{assetLink('image','mflags')}});
            --iti-path-flags-2x: url({{assetLink('image','mflags2x')}});
            --iti-path-globe-1x: url({{assetLink('image','mglobe')}});
            --iti-path-globe-2x: url({{assetLink('image','mglobe2x')}});
        }
    </style>

    @vite(['app/Modules/License/Resources/css/app.scss', 'app/Modules/License/Resources/js/app.js'])

    <script src="{{ url('/license-manager-lang') }}" type="text/javascript"></script>
</head>

<body class="layout-fixed layout-navbar-fixed sidebar-expand-lg bg-body-tertiary app-loaded sidebar-collapse">
<div id="app">

    <license-manager-renderer
            :versioning="{{ json_encode($versioning) }}"
            :user-data="{{ json_encode($userData) }}"
    >
    </license-manager-renderer>
</div>

<script type="text/javascript" src="{{ assetLink('js','popper') }}"></script>

<script src="{{ assetLink('js','bootstrap-5') }}" type="text/javascript"></script>

<script src="{{ assetLink('js','adminlte-4') }}" type="text/javascript"></script>

<script src="{{ assetLink('js','new-overlay') }}" type="text/javascript"></script>

</body>
</html>
