<style>
    .visitortracker-icon {
        height:20px; 
        width:20px;
    }
</style>
@if (!isset($hideUserAndIp))
    @if ($visit->user_id)
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/user.png') }}"
            title="{{ __('message.authenticated_user') }} {{ $visit->user_email }}">
    @endif

    @if ($visit->is_bot)
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/spider.png') }}"
             title="{{ $visit->bot ?: __('message.bot') }}"
    @endif

    {{ $visit->ip }}

    <br>
@endif

@if ($visit->os_family)
    @if (file_exists(public_path('vendor/visitortracker/icons/os/'.$visit->os_family.'.png')))
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/os/'.$visit->os_family.'.png') }}"
            title="{{ $visit->os }}"
            alt="{{ $visit->os }}">
    @else
        <span>{{ $visit->os }}</span>
    @endif
@else
    <span>{{ $visit->os }}</span>
@endif

@if ($visit->browser_family)
    @if (file_exists(public_path('vendor/visitortracker/icons/browsers/'.$visit->browser_family.'.png')))
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/browsers/'.$visit->browser_family.'.png') }}"
            title="{{ $visit->browser }}"
            alt="{{ $visit->browser }}">
    @else
        <span>{{ $visit->browser }}</span>
    @endif
@else
    <span>{{ $visit->browser }}</span>
@endif

@if ($visit->is_desktop)
    <img class="visitortracker-icon"
        src="{{ asset('/vendor/visitortracker/icons/desktop.png') }}"
        title="{{ __('message.desktop') }}">
@endif

@if ($visit->is_mobile)
    <img class="visitortracker-icon"
        src="{{ asset('/vendor/visitortracker/icons/mobile.png') }}"
        title="{{ __('message.mobile_device') }}">
@endif

{{ $visit->browser_language ?: '' }}

<br>

@if ($visit->country_code)
    @if (file_exists(public_path('vendor/visitortracker/icons/flags/'.$visit->country_code.'.png')))
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/flags/'.$visit->country_code.'.png') }}"
            title="{{ $visit->country }}">
    @else
        <img class="visitortracker-icon"
            src="{{ asset('/vendor/visitortracker/icons/flags/unknown.png') }}"
            title="{{ __('message.unknown') }}">
    @endif
@endif

{{ $visit->city ?: '' }}{{ $visit->city && $visit->lat && $visit->long ? ',' : '' }}

@if ($visit->lat && $visit->long)
    {{ $visit->lat }}, {{ $visit->long }}
@endif