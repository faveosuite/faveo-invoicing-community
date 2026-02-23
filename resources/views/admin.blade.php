<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Admin Panel') }}</title>

    @foreach(config('theme.css') as $css)
        @if(config('theme.use_cdn'))
            <link rel="stylesheet" href="{{ $css }}">
        @else
            <link rel="stylesheet" href="{{ asset($css) }}">
        @endif
    @endforeach

    @vite(['resources/js/main.js'])
</head>
<body>
    <div
        id="app-root"
        data-theme="{{ config('theme.active') }}"
        data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
        data-base-url="{{ url('/') }}"
        data-admin-url="{{ url('/admin') }}"
    ></div>

    @foreach(config('theme.js') as $js)
        @if(config('theme.use_cdn'))
            <script src="{{ $js }}"></script>
        @else
            <script src="{{ asset($js) }}"></script>
        @endif
    @endforeach

</body>
</html>
