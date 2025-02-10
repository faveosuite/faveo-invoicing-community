<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('installer_messages.title') }}</title>

    <link rel="shortcut icon" href="{{ asset('images/faveo.png') }}" type="image/x-icon" />
    <link href="{{ asset('admin/css-1/all.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/css-1/flag-icons.min.css') }}" rel="stylesheet" type="text/css" />

    @if(app()->getLocale()=='ar')
        <link href="{{ asset('admin/css-1/probe-rtl.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/css-1/adminlte-rtl.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/css-1/bs-stepper-rtl.css') }}" rel="stylesheet" type="text/css" />
    @else
        <link href="{{ asset('admin/css-1/adminlte.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/css-1/bs-stepper.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('admin/css-1/probe.css') }}" rel="stylesheet" type="text/css" />
    @endif

</head>

<body class="layout-top-nav text-sm layout-navbar-fixed layout-footer-fixed" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

@php
    $currentPath = basename(request()->path());
@endphp

<div class="wrapper" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
{{--    Header Component--}}
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">

        <div class="container d-flex justify-content-center align-items-center">

            <a href="javascript:;" class="navbar-brand" style="">

                <img src="{{ asset('images/agora-invoicing.png') }}" alt="Agora Logo" class="brand-image install-img">
            </a>
            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" id="languageButton" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        <i id="flagIcon" class="flag-icon flag-icon-us"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right p-0" style="left: inherit; right: 0px;" id="language-dropdown">
                        <!-- Language options will be populated here -->
                    </div>
                </li>
            </ul>
        </div>
    </nav>

{{--    Steppers--}}
    <div class="content-wrapper" style="min-height: 950px !important;">

        <div class="container pt-3 pb-3">

            <div class="accordion" id="accordionExample">

                <div class="steps mt-5">
                    <progress id="progress" value=0 max=100></progress>

                    <div class="step-item">

                        <button id="btn-server" class="step-button text-center cursor-default" type="button" aria-expanded="true" aria-controls="server">
                            1
                        </button>

                        <div class="step-title mt-2 text-bold">{{ trans('installer_messages.server_requirements') }}</div>
                    </div>

                    <div class="step-item">

                        <button id="btn-database" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="database">
                            2
                        </button>

                        <div class="step-title mt-2">{{ __('installer_messages.database_setup') }}</div>
                    </div>

                    <div class="step-item">

                        <button id="btn-start" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="start">
                            3
                        </button>

                        <div class="step-title mt-2">{{ __('installer_messages.getting_started') }}</div>
                    </div>

                    <div class="step-item">

                        <button id="btn-final" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="final">
                            4
                        </button>

                        <div class="step-title mt-2">{{ __('installer_messages.final') }}</div>
                    </div>

                </div>

                <div class="setup-content">
                    @yield('content')
                </div>

                </div>
                </div>
                </div>
    <footer class="main-footer">
        @php
            $config = config('app');
        @endphp

        <div class="float-right d-none d-sm-inline">Agora Invoicing <?php echo $config['version']; ?></div>

        <strong>Copyright © 2014-2021 <a href="javascript:;">Ladybird Web Solution Pvt Ltd.</a>.</strong> All rights reserved.
    </footer>
</div>


<script src="{{ asset('admin/js/jquery.min.js') }}"></script>
<script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/js/adminlte.min.js') }}"></script>
<script src="{{ asset('admin/js/bs-stepper.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>

{{--handle api--}}
<script type="module">
    // var body = document.body;
    // var currentDir = body.getAttribute('dir');

    function mapEndpointToValue(endpoint) {
        const manualMappings = {
            probe: 'server',
            'db-setup': 'database',
            'post-check' : 'database',
            'get-start': 'start',
            final: 'final',
        };
        const manualMatch = Object.keys(manualMappings).find(key => endpoint.includes(key));
        return manualMappings[manualMatch];
    }

    //Stepper Process
    gotoStep('{{ $currentPath }}');
    function gotoStep(value) {
        value = mapEndpointToValue(value);
        const progress = document.querySelector('#progress');
        const steps = ['server',  'database', 'start', 'final'];
        const progressValues = {
            server: 0,
            database: 35,
            start: 70,
            final: 100
        };

        progress.value = progressValues[value] || 0;

        const currentStepIndex = steps.indexOf(value);

        steps.forEach((step, index) => {
            const btnStep = document.getElementById(`btn-${step}`);
            const titleElement = btnStep?.nextElementSibling;

            if (btnStep) {
                btnStep.setAttribute('aria-expanded', step === value);

                btnStep.style.backgroundColor = index <= currentStepIndex ? '#3AA7D9' : '';

                if (titleElement) {
                    titleElement.classList.toggle('text-bold', step === value);
                }
            }
        });
    }

    const flagIcon = document.getElementById('flagIcon');
    const languageDropdown = document.getElementById('language-dropdown');

    $(document).ready(function() {
        $.ajax({
            url: '<?php echo getUrl() ?>/language/settings',
            type: 'GET',
            dataType: 'JSON',
            success: function(response) {
                const localeMap = { 'ar': 'ae', 'bsn': 'bs', 'de': 'de', 'en': 'us', 'en-gb': 'gb', 'es': 'es', 'fr': 'fr', 'id': 'id', 'it': 'it', 'kr': 'kr', 'mt': 'mt', 'nl': 'nl', 'no': 'no', 'pt': 'pt', 'ru': 'ru', 'vi': 'vn', 'zh-hans': 'cn', 'zh-hant': 'cn' };
                $.each(response.data, function(key, value) {
                    const mappedLocale = localeMap[value.locale] || value.locale;
                    const isSelected = value.locale === '{{ app()->getLocale() }}' ? 'selected' : '';
                    $('#language-dropdown').append(
                        '<a href="javascript:;" class="dropdown-item" data-locale="' + value.locale + '" ' + isSelected + '>' +
                        '<i class="flag-icon flag-icon-' + mappedLocale + ' mr-2"></i> ' + value.name +
                        '</a>'
                    );
                });

                // Add event listeners for the dynamically added language options
                $(document).on('click', '.dropdown-item', function() {
                    const selectedLanguage = $(this).data('locale');
                    const mappedLocale = localeMap[selectedLanguage] || selectedLanguage;
                    const flagClass = 'flag-icon flag-icon-' + mappedLocale;
                    const dir = selectedLanguage === 'ar' ? 'rtl' : 'ltr';

                    updateLanguage(selectedLanguage, flagClass, dir);
                });
            },
            error: function(error) {
                console.error('Error fetching languages:', error);
            }
        });

        $.ajax({
            url: '<?php echo getUrl() ?>/current-language',
            type: 'GET',
            dataType: 'JSON',
            success: function(response) {
                const localeMap = { 'ar': 'ae', 'bsn': 'bs', 'de': 'de', 'en': 'us', 'en-gb': 'gb', 'es': 'es', 'fr': 'fr', 'id': 'id', 'it': 'it', 'kr': 'kr', 'mt': 'mt', 'nl': 'nl', 'no': 'no', 'pt': 'pt', 'ru': 'ru', 'vi': 'vn', 'zh-hans': 'cn', 'zh-hant': 'cn' };
                const currentLanguage = response.data.language;
                const flagClass = 'flag-icon flag-icon-' + localeMap[currentLanguage];
                $('#flagIcon').attr('class', flagClass);
                const dir = currentLanguage === 'ar' ? 'rtl' : 'ltr';

                document.body.setAttribute('dir', dir);

                $('head').append('<meta name="csrf-token" content="{{ csrf_token() }}">');
                if (currentLanguage === 'ar') {
                    {{--$('head').append('<link href="{{ asset('admin/css-1/probe-rtl.css') }}" rel="stylesheet" type="text/css" />');--}}
                    {{--$('head').append('<link href="{{ asset('admin/css-1/adminlte-rtl.css') }}" rel="stylesheet" type="text/css" />');--}}
                    {{--$('head').append('<link href="{{ asset('admin/css-1/bs-stepper-rtl.css') }}" rel="stylesheet" type="text/css" />');--}}
                    const arrowElements = document.getElementsByClassName('fas fa-arrow-right');
                    for (let i = 0; i < arrowElements.length; i++) {
                        arrowElements[i].className = 'fas fa-arrow-left';
                    }
                    const setClassName = (elements, className) => {
                        Array.from(elements).forEach(element => {
                            element.className = className;
                        });
                    };

                    setClassName(document.getElementsByClassName('continue'), 'fas fa-arrow-left');
                    setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-right');
                } else {
                    {{--$('head').append('<link href="{{ asset('admin/css-1/adminlte.min.css') }}" rel="stylesheet" type="text/css" />');--}}
                    {{--$('head').append('<link href="{{ asset('admin/css-1/bs-stepper.css') }}" rel="stylesheet" type="text/css" />');--}}
                    {{--$('head').append('<link href="{{ asset('admin/css-1/probe.css') }}" rel="stylesheet" type="text/css" />');--}}
                    const arrowElements = document.getElementsByClassName('fas fa-arrow-left');
                    for (let i = 0; i < arrowElements.length; i++) {
                        arrowElements[i].className = 'fas fa-arrow-right';
                    }
                    const setClassName = (elements, className) => {
                        Array.from(elements).forEach(element => {
                            element.className = className;
                        });
                    };

                    const progressElement = document.getElementById('progress');

                    if (currentLanguage === 'ar') {
                        progressElement.style.marginRight = '25px !important';
                        progressElement.style.width = '95%';
                    } else if (currentLanguage === 'zh-hant' || currentLanguage === 'zh-hans' || currentLanguage === 'kr') {
                        progressElement.style.marginLeft = '18px';
                        progressElement.style.width = '96%';
                    } else if (currentLanguage === 'no') {
                        progressElement.style.marginLeft = '20px';
                        progressElement.style.width = '96%';
                    }else{
                        progressElement.style.width = '91%';
                    }

                    setClassName(document.getElementsByClassName('continue'), 'continue fas fa-arrow-right');
                    setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-left');

                }
            },
            error: function(error) {
                console.error('Error fetching current language:', error);
            }
        });
    });

    function updateLanguage(language, flagClass, dir) {
        $('#flagIcon').attr('class', flagClass);
        // $('body').attr('dir', dir);
        $.ajax({
            url: '<?php echo getUrl() ?>/update/language',
            type: 'POST',
            data: { language: language },
            success: function(response) {
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error updating language:', xhr.responseText);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = '{{ $currentPath }}';
        const languageButton = document.getElementById('languageButton');

        if (currentPath === 'post-check' || currentPath === 'final') {
            languageButton.classList.add('disabled');
            languageButton.setAttribute('aria-disabled', 'true');
            languageButton.style.pointerEvents = 'none';
        } else {
            languageButton.classList.remove('disabled');
            languageButton.removeAttribute('aria-disabled');
            languageButton.style.pointerEvents = 'auto';
        }
    });

</script>


</body>
</html>
