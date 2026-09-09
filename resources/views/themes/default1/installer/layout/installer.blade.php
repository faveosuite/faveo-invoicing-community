@php $isRtl = in_array(app()->getLocale(), ['ar', 'he']); @endphp
<!Doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('installer_messages.title') }}</title>

    <link rel="shortcut icon" href="{{ asset('images/faveo.png') }}" type="image/x-icon" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('themes/default/common/bootstrap/css/' . ($isRtl ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('themes/default/common/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/common/flag-icons/css/flag-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/admin/adminlte/css/' . ($isRtl ? 'adminlte.rtl.min.css' : 'adminlte.min.css')) }}">
    <style>
        .wizard-steps { counter-reset: step; list-style: none; margin: 0; padding: 0; display: flex; justify-content: space-around; align-items: flex-start; }
        .wizard-steps li { flex: 1; position: relative; padding: 0 0.75rem; margin: 0; text-align: center; color: var(--bs-secondary-color); font-size: 0.875rem; }
        .wizard-steps li::before { counter-increment: step; content: counter(step); display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; margin: 0 auto 0.5rem; border-radius: 50%; background: #adb5bd; border: 2px solid #adb5bd; color: #fff; font-weight: 600; font-size: 1.1rem; position: relative; z-index: 1; }
        .wizard-steps li:not(:last-child)::after { content: ''; position: absolute; top: 1.5rem; left: 50%; width: 100%; height: 2px; background: var(--bs-border-color); z-index: 0; }
        .wizard-steps li.active { color: var(--bs-primary); font-weight: 600; }
        .wizard-steps li.active::before { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
        .wizard-steps li.completed::before { background: var(--bs-success); border-color: var(--bs-success); color: #fff; content: '\2713'; }
        .wizard-steps li.completed:not(:last-child)::after { background: var(--bs-success); }
    </style>

</head>

<body class="layout-fixed bg-body-tertiary">

@php
    $currentPath = basename(request()->path());
@endphp

<div class="app-wrapper">
{{--    Header Component--}}
    <nav class="app-header navbar navbar-expand bg-body">

        <div class="container">
            <div class="col-xl-10 mx-auto d-flex align-items-center">
            <a href="javascript:;" class="navbar-brand">
                <img src="{{ asset('images/agora-invoicing.png') }}" alt="Agora Logo" style="height:50px;">
            </a>
            <ul class="navbar-nav ms-auto">
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
                                'zh-hant' => 'cn',
                                'ja' => 'jp',
                                'ta' => 'in',
                                'hi' => 'in',
                                'he' => 'il',
                                'tr' => 'tr',
                            ];

                            $currentLanguage = app()->getLocale();
                            $flagClass = 'fi fi-' . strtolower($localeMap[$currentLanguage] ?? 'us');
                            ?>
                            <i id="flagIcon" class="<?= $flagClass ?>"></i>
                        </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" id="language-dropdown">
                        <!-- Language options will be populated here -->
                    </div>
                </li>
            </ul>
            </div><!-- /.col-xl-10 -->
        </div><!-- /.container -->
    </nav>

    <main class="app-main">
    <div class="app-content">
        <div class="container py-3">
            <div class="col-xl-10 mx-auto">

                <ol class="wizard-steps mb-4">
                    <li class="active" id="server">{{__('installer_messages.server_requirements')}}</li>
                    <li id="database">{{__('installer_messages.database_setup')}}</li>
                    <li id="start">{{__('installer_messages.getting_started')}}</li>
                    <li id="final">{{__('installer_messages.final')}}</li>
                </ol>

                <div id="alert-container"></div>

                @yield('content')

            </div>
        </div>
    </div>
    </main>
    <footer class="app-footer">
        @php
            $config = config('app');
        @endphp

        <div class="float-end d-none d-sm-inline">Agora Invoicing <?php echo $config['version']; ?></div>

        <strong>{{trans('installer_messages.copyright')}} © 2015 - <?= date('Y') ?> <span class="cursor-normal text-primary">Ladybird Web Solution Pvt Ltd.</span></strong> {{trans('installer_messages.powered_by')}} <strong><a href="https://www.faveohelpdesk.com/" target="_blank">Faveo</a></strong>
    </footer>
</div>

<script src="{{ asset('themes/default/common/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('themes/default/admin/adminlte/js/adminlte.min.js') }}"></script>

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

    const localeMap = { 'ar': 'ae', 'bsn': 'bs', 'de': 'de', 'en': 'us', 'en-gb': 'gb', 'es': 'es', 'fr': 'fr', 'id': 'id', 'it': 'it', 'kr': 'kr', 'mt': 'mt', 'nl': 'nl', 'no': 'no', 'pt': 'pt', 'ru': 'ru', 'vi': 'vn', 'zh-hans': 'cn', 'zh-hant': 'cn', 'ja': 'jp', 'ta': 'in', 'hi': 'in', 'he': 'il', 'tr': 'tr' };

    function updateLanguage(language, flagClass) {
        document.getElementById('flagIcon').className = flagClass;
        fetch('{{ url('update/language') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: new URLSearchParams({ language }),
        })
        .then(() => window.location.reload())
        .catch(err => console.error('Error updating language:', err));
    }

    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ url('language/settings') }}')
            .then(r => r.json())
            .then(function (response) {
                const dropdown = document.getElementById('language-dropdown');
                response.data.forEach(function (value) {
                    const mappedLocale = localeMap[value.locale] || value.locale;
                    const isSelected = value.locale === '{{ app()->getLocale() }}' ? 'selected' : '';
                    dropdown.insertAdjacentHTML('beforeend',
                        '<a href="javascript:;" class="dropdown-item" data-locale="' + value.locale + '" ' + isSelected + '>' +
                        '<i class="fi fi-' + mappedLocale.toLowerCase() + ' me-2"></i> ' + value.name + ' (' + value.translation + ')' +
                        '</a>'
                    );
                });
            })
            .catch(err => console.error('Error fetching languages:', err));

        document.addEventListener('click', function (e) {
            const item = e.target.closest('.dropdown-item');
            if (!item) return;
            const selectedLanguage = item.dataset.locale;
            updateLanguage(selectedLanguage, 'fi fi-' + (localeMap[selectedLanguage] || selectedLanguage).toLowerCase());
        });

        const currentLanguage = '{{ app()->getLocale() }}';
        const setClassName = (elements, className) => Array.from(elements).forEach(el => { el.className = className; });
        const updateButtonText = (selector, iconClass, position) => {
            document.querySelectorAll(selector).forEach(button => {
                button.innerHTML = position === 'left'
                    ? `<i class="${iconClass}"></i>&nbsp;${button.innerHTML}`
                    : `${button.innerHTML}&nbsp;<i class="${iconClass}"></i>`;
            });
        };

        if (currentLanguage === 'ar' || currentLanguage === 'he') {
            setClassName(document.getElementsByClassName('continue'), 'fas fa-arrow-left');
            setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-right');
            updateButtonText('.previous', 'fas fa-arrow-right', 'left');
            updateButtonText('.continue', 'fas fa-arrow-left', 'right');
        } else {
            setClassName(document.getElementsByClassName('continue'), 'continue fas fa-arrow-right');
            setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-left');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = '{{ $currentPath }}';
        const languageButton = document.getElementById('languageButton');

        if (currentPath === 'post-check') {
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
