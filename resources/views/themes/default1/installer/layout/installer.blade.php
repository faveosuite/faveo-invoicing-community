<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('installer_messages.title') }}</title>

    <link rel="shortcut icon" href="{{ asset('images/faveo.png') }}" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="client/css/._fontawesome-all.min.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <?php
    $css_files = [
        ['file' => './admin/css-1/all.min.css'],
        ['file' => './admin/css-1/flag-icons.min.css'],
        ['file' => './admin/css-1/probe.css', 'id' => 'default-styles-1'],
    ];

    $rtl = [
        ['file' => './admin/css-1/adminlte-rtl.css', 'id' => 'rtl-styles'],
        ['file' => './admin/css-1/bs-stepper-rtl.css', 'id' => 'rtl-styles-2'],
        ['file' => './admin/css-1/probe-rtl.css', 'id' => 'rtl-styles-1'],
    ];

    $ltr = [
        ['file' => './admin/css-1/adminlte.min.css', 'id' => 'default-styles'],
        ['file' => './admin/css-1/bs-stepper-rtl.css', 'id' => 'rtl-styles-2'], // Seems incorrect (should be 'bs-stepper.css'?)
        ['file' => './admin/css-1/probe.css', 'id' => 'default-styles-1'],
    ];
    $locale = app()->getLocale();
    $selected_files = ($locale == 'ar') ? array_merge($css_files, $rtl) : array_merge($css_files, $ltr);

    // Output styles
    foreach ($selected_files as $css) {
        $id = isset($css['id']) ? ' id="' . $css['id'] . '"' : '';
        echo '<link rel="stylesheet" href="' . $css['file'] . '"' . $id . '>' . PHP_EOL;
    }
    ?>

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
                            <?php
                            $localeMap = [
                                'ar' => 'ae',
                                'bsn' => 'bs',
                                'de' => 'de',
                                'en' => 'us',
                                'en-gb' => 'gb',
                                'es' => 'es',
                                'fr' => 'fr',
                                'id' => 'id',
                                'it' => 'it',
                                'kr' => 'kr',
                                'mt' => 'mt',
                                'nl' => 'nl',
                                'no' => 'no',
                                'pt' => 'pt',
                                'ru' => 'ru',
                                'vi' => 'vn',
                                'zh-hans' => 'cn',
                                'zh-hant' => 'cn'
                            ];

                            $currentLanguage = app()->getLocale();
                            $flagClass = 'flag-icon flag-icon-' . $localeMap[$currentLanguage];
                            ?>
                            <i id="flagIcon" class="<?= $flagClass ?>"></i>
                        </a>
                    <div class="dropdown-menu dropdown-menu-right p-0" style="left: inherit; right: 0px;" id="language-dropdown">
                        <!-- Language options will be populated here -->
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="content-wrapper" style="min-height: 950px !important;">

        <div class="container pt-3 pb-3">
            <div class="accordion" id="accordionExample">
                <div class="option-1 option-1-1 mt-5">
                    <ol class="c-stepper p-3">
                        <li class="c-stepper__item active" id="server">
                            <div class="c-stepper__icon">1</div>
                            <p class="c-stepper__title fs-6">{{__('installer_messages.server_requirements')}}</p>
                        </li>
                        <li class="c-stepper__item" id="database">
                            <div class="c-stepper__icon">2</div>
                            <p class="c-stepper__title fs-6">{{__('installer_messages.database_setup')}}</p>
                        </li>
                        <li class="c-stepper__item" id="start">
                            <div class="c-stepper__icon">3</div>
                            <p class="c-stepper__title fs-6">{{__('installer_messages.getting_started')}}</p>
                        </li>
                        <li class="c-stepper__item" id="final">
                            <div class="c-stepper__icon">4</div>
                            <p class="c-stepper__title fs-6">{{__('installer_messages.final')}}</p>
                        </li>
                    </ol>
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

        <strong>{{trans('installer_messages.copyright')}} © 2015 - <?= date('Y') ?> <span class="cursor-normal text-primary">Ladybird Web Solution Pvt Ltd.</span></strong> {{trans('installer_messages.powered_by')}} <strong><a href="https://www.faveohelpdesk.com/" target="_blank">Faveo</a></strong>
    </footer>
</div>

<script src="{{ asset('admin/js/jquery.min.js') }}"></script>
<script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/js/adminlte.min.js') }}"></script>
<script src="{{ asset('admin/js/bs-stepper.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>

{{--handle api--}}
<script type="module">

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
        const steps = ['server', 'database', 'start', 'final'];

        const currentStepIndex = steps.indexOf(value);

        steps.forEach((step, index) => {
            const stepper = document.getElementById(`${step}`);
            if (stepper) {
                stepper.classList.remove('active', 'completed'); // Reset classes

                if (currentStepIndex > index) {
                    stepper.classList.add('completed'); // Mark previous steps as completed
                }
                if (currentStepIndex === index) {
                    stepper.classList.add('active'); // Mark current step as active
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
                        '<i class="flag-icon flag-icon-' + mappedLocale + ' mr-2"></i> ' + value.name + ' (' + value.translation + ')' +
                        '</a>'
                    );
                });

                // Add event listeners for the dynamically added language options
                $(document).on('click', '.dropdown-item', function() {
                    const selectedLanguage = $(this).data('locale');
                    const mappedLocale = localeMap[selectedLanguage] || selectedLanguage;
                    const flagClass = 'flag-icon flag-icon-' + mappedLocale;
                    updateLanguage(selectedLanguage, flagClass);
                });
            },
            error: function(error) {
                console.error('Error fetching languages:', error);
            }
        });

        const currentLanguage = '{{ app()->getLocale() }}';
        const setClassName = (elements, className) => {
            Array.from(elements).forEach(element => {
                element.className = className;
            });
        };

        const updateButtonText = (selector, iconClass, position) => {
            document.querySelectorAll(selector).forEach(button => {
                if (position === 'left') {
                    button.innerHTML = `<i class="${iconClass}"></i>&nbsp;${button.innerHTML}`;
                } else {
                    button.innerHTML = `${button.innerHTML}&nbsp;<i class="${iconClass}"></i>`;
                }
            });
        };

        if (currentLanguage === 'ar') {
            setClassName(document.getElementsByClassName('fas fa-arrow-right'), 'fas fa-arrow-left');
            setClassName(document.getElementsByClassName('continue'), 'fas fa-arrow-left');
            setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-right');
            updateButtonText('.previous', 'fas fa-arrow-right', 'left');
            updateButtonText('.continue', 'fas fa-arrow-left', 'right');
        } else {
            setClassName(document.getElementsByClassName('fas fa-arrow-left'), 'fas fa-arrow-right');
            setClassName(document.getElementsByClassName('continue'), 'continue fas fa-arrow-right');
            setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-left');
        }
    });

    function updateLanguage(language, flagClass) {
        $('#flagIcon').attr('class', flagClass);
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
