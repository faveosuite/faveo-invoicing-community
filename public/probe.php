<?php
require __DIR__.'/../bootstrap/autoload.php';
$config = require_once '../config/app.php';
use App\Http\Controllers\BillingInstaller\BillingDependencyController;
require_once dirname(__DIR__, 1).'/app/Http/helpers.php';


$passwordMatched = false;
$showError = false;
$env = '../.env';
$envFound = is_file($env);
//$isProbeAccess = 1;
//if ($envFound) {
//    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
//    $dotenv->load();
//    $isProbeAccess = (int)env('DB_INSTALL') === 1 ? 0 : 1;
//}
if ($envFound) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
    $dotenv->load();
}
if (isset($_POST['submit'])) {

    $probePhrase = env('PROBE_PASS_PHRASE', '   ');

    $input = $_POST['passPhrase'];
    if (! in_array($input, [$probePhrase])) {
        $showError = true;
    } else {
        $passwordMatched = true;
    }
}

//function getBaseUrl() {
//    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
//    $host = $_SERVER['HTTP_HOST'];
//    $path = dirname($_SERVER['SCRIPT_NAME']);
//    return $protocol . '://' . $host . $path;
//}
function fetchLang() {
    //$baseUrl = getBaseUrl();
    $langUrl = getUrl() . "/lang";
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json;charset=UTF-8\r\n",
            'content' => ''
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($langUrl, false, $context);

    if ($response === false) {
        return null;
    }

    $langData = json_decode($response, true);

    return $langData['data'];
}

$lang = fetchLang();

?>
<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $lang['title'] ?></title>

    <link rel="shortcut icon" href="./images/faveo.png" type="image/x-icon" />
    <link href="admin/css-1/all.min.css" rel="stylesheet" type="text/css" />
    <link href="admin/css-1/flag-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="client/css/._fontawesome-all.min.css" rel="stylesheet" type="text/css" />

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <style>
        .cursor-default { cursor: default !important; }
        .timeline::before {
            bottom: 10px;
        }
        .text-bold,
        .active {
            font-weight: bold;
        }
        /*This is added because of the eye icon is automatically added in edge browser*/
        input[type="password"]::-ms-reveal {
            display: none !important;
        }
        .form-control.is-invalid{
            background-image: none !important;
        }
    </style>
</head>

<body class="layout-top-nav text-sm layout-navbar-fixed layout-footer-fixed" dir="ltr">

<div class="wrapper">

    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">

        <div class="container d-flex justify-content-center align-items-center">

            <a href="javascript:;" class="navbar-brand" style="">

                <img src="./images/agora-invoicing.png" alt="Agora Logo" class="brand-image install-img">
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

    <?php
    if ($envFound && ! $passwordMatched){
        ?>
        <div class="content-wrapper" style="margin-top: 80px;">
            <div class="content">
                <div class="container pt-3 pb-3">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <form action="probe.php" method="post">
                                <div class="card-body">
                                    <p class="text-center lead text-bold"><?= $lang['title'] ?></p>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="inputEmail1" class="col-sm-12 col-form-label">
                                                <?= $lang['magic_phrase'] ?><span style="color: red;">*</span>
                                            </label>
                                            <input type="password"
                                                   class="form-control <?= $showError ? 'is-invalid' : '' ?>"
                                                   id="phrase"
                                                   name="passPhrase"
                                                   placeholder="Enter magic phrase"
                                                   value="<?= isset($_POST['passPhrase']) ? htmlspecialchars($_POST['passPhrase']) : '' ?>">

                                            <?php if (isset($showError) && $showError): ?>
                                                <span class="error invalid-feedback"><?= $lang['magic_phrase_not_work'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">

                                    <button class="btn btn-primary float-right" name="submit" id="magic-phrase-submit">
                                        <?= $lang['continue'] ?>&nbsp;
                                        <i class="fas fa-arrow-right continue"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.getElementById("magic-phrase-submit").addEventListener("click", function(event) {
                let inputField = document.getElementById("phrase");
                let passPhrase = inputField.value.trim();
                let errorMessage = "<?= addslashes($lang['magic_required']) ?>";

                if (passPhrase === "") {
                    event.preventDefault();
                    showError(inputField, errorMessage);
                } else {
                    removeError(inputField);
                }
            });

            const showError = (field, message) => {
                field.classList.add('is-invalid');
                const existingError = field.nextElementSibling;
                if (existingError && existingError.classList.contains('error')) {
                    existingError.remove();
                }

                const errorSpan = document.createElement('span');
                errorSpan.className = 'error invalid-feedback';
                errorSpan.innerText = message;
                field.after(errorSpan);
            };

            const removeError = (field) => {
                field.classList.remove('is-invalid');
                const existingError = field.nextElementSibling;
                if (existingError && existingError.classList.contains('error')) {
                    existingError.remove();
                }
            };

        </script>

    <?php } else { ?>
        <div class="content-wrapper" style="margin-top: 80px;">

            <div class="content">

                <div class="container pt-3 pb-3">

                    <div class="accordion" id="accordionExample">

                        <div class="steps">

                            <progress id="progress" value=0 max=100></progress>

                            <div class="step-item">

                                <button id="btn-server" class="step-button text-center cursor-default" type="button" aria-expanded="true" aria-controls="server">
                                    1
                                </button>

                                <div class="step-title mt-2 text-bold"><?= $lang['server_requirements'] ?></div>
                            </div>

                            <div class="step-item">

                                <button id="btn-database" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="database">
                                    2
                                </button>

                                <div class="step-title mt-2"><?= $lang['database_setup'] ?></div>
                            </div>

                            <div class="step-item">

                                <button id="btn-start" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="start">
                                    3
                                </button>

                                <div class="step-title mt-2"><?= $lang['getting_started'] ?></div>
                            </div>

                            <div class="step-item">

                                <button id="btn-final" class="step-button text-center collapsed cursor-default" type="button" aria-expanded="false" aria-controls="final">
                                    4
                                </button>

                                <div class="step-title mt-2"><?= $lang['final'] ?></div>
                            </div>
                        </div>

                        <div id="alert-container"></div>

                        <div id="server" class="collapse show" role="tabpanel" data-bs-parent="#accordionExample">

                            <div class="card">

                                <div class="card-body">

                                    <p class="text-center lead text-bold"><?= $lang['server_requirements'] ?></p>


                                    <div class="row">

                                        <table class="table table-bordered">

                                            <thead style="background: #f5f5f5;">

                                            <tr><th style="width:50%;"><?= $lang['directory'] ?></th><th><?= $lang['permissions'] ?></th></tr>
                                            </thead>

                                            <tbody>
                                            <?php
                                            $errorCount = 0;
                                            $basePath = substr(__DIR__, 0, -6);
                                            $billingController = new BillingDependencyController('probe');
                                            $details = $billingController->validateDirectory($basePath, $errorCount);

                                            foreach ($details as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['extensionName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-<?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?= htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">

                                        <table class="table table-bordered">

                                            <thead style="background: #f5f5f5;">

                                            <tr><th style="width:50%;"><?= $lang['requisites'] ?></th><th><?= $lang['status'] ?></th></tr>
                                            </thead>

                                            <tbody>
                                            <?php
                                            $details = (new BillingDependencyController('probe'))->validateRequisites($errorCount);
                                            foreach ($details as $detail): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($detail['extensionName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td style="color: <?= htmlspecialchars($detail['color'], ENT_QUOTES, 'UTF-8'); ?>;">
                                                        <?= $detail['connection']; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">

                                        <table class="table table-bordered">

                                            <thead style="background: #f5f5f5;">

                                            <tr><th style="width:50%;"><?= $lang['php_extensions'] ?></th><th><?= $lang['status'] ?></th></tr>
                                            </thead>

                                            <tbody>
                                            <?php
                                            $details = (new BillingDependencyController('probe'))->validatePHPExtensions($errorCount);

                                            $extString = $lang['extension_not_enabled'];

                                            $phpIniFile = php_ini_loaded_file();
                                            $extensionName = $detail['extensionName'];
                                            $url = 'https://support.faveohelpdesk.com/show/how-to-enable-required-php-extension-on-different-servers-for-faveo-installation';

                                            $extString = str_replace(
                                                [':php_ini_file', ':extensionName', ':url'],
                                                [$phpIniFile, $extensionName, $url],
                                                $extString
                                            );
                                            foreach ($details as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['extensionName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="<?= $item['key'] === 'no-error' ? 'text-success' : 'text-warning'; ?>">
                                                        <?= $item['key'] === 'no-error' ? 'Enabled' : $extString; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">

                                        <table class="table table-bordered">

                                            <thead style="background: #f5f5f5;">

                                            <tr><th style="width:50%;"><?= $lang['mod_rewrite'] ?></th><th><?= $lang['status'] ?></th></tr>
                                            </thead>
                                            <?php
                                            function getLicenseUrl()
                                            {
                                                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                                                    $url = 'https://';
                                                } else {
                                                    $url = 'http://';
                                                }
                                                // Append the host(domain name, ip) to the URL.
                                                $url .= $_SERVER['HTTP_HOST'];

                                                // Append the requested resource location to the URL
                                                $url .= $_SERVER['REQUEST_URI'];

                                                return str_replace('probe.php', 'db-setup', $url);
                                            }
                                            function checkUserFriendlyUrl()
                                            {
                                                if (function_exists('curl_init') === true) {
                                                    try {
                                                        $ch = curl_init(getLicenseUrl());
                                                        curl_setopt($ch, CURLOPT_HEADER, true);
                                                        curl_setopt($ch, CURLOPT_NOBODY, true);
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                                                        curl_exec($ch);
                                                        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                                        curl_close($ch);

                                                        return $httpcode != 404;
                                                    } catch (Exception $e) {
                                                        return null;
                                                    }
                                                }

                                                return null;
                                            }

                                            // Rewrite Engine Check
                                            $redirect = function_exists('apache_get_modules') ? (int) in_array('mod_rewrite', apache_get_modules()) : 2;
                                            $rewriteStatusColor = 'green';
                                            $rewriteStatusString = 'Enabled';
                                            if ($redirect == 2) {
                                                $rewriteStatusColor = '#F89C0D';
                                                $rewriteStatusString = 'Unable to detect';
                                            } elseif (!$redirect) {
                                                $errorCount++;
                                                $rewriteStatusColor = 'red';
                                                $rewriteStatusString = 'OFF';
                                            }

                                            // User Friendly URL Check
                                            $userFriendlyUrl = checkUserFriendlyUrl();
                                            $userFriendlyUrlStatusColor = 'green';
                                            $userFriendlyUrlStatusString = 'Enabled';

                                            if ($userFriendlyUrl === false) {
                                                $errorCount++;
                                                $userFriendlyUrlStatusColor = 'red';
                                                $userFriendlyUrlStatusString = $lang['off_apache'];
                                            } elseif ($userFriendlyUrl !== true) {
                                                $userFriendlyUrlStatusColor = '#F89C0D';
                                                $userFriendlyUrlStatusString = 'Unable to detect';
                                            }
                                            ?>
                                            <tbody>
                                            <tr>
                                                <td><?= $lang['rewrite_engine'] ?></td>
                                                <td style='color: <?= htmlspecialchars($rewriteStatusColor, ENT_QUOTES, 'UTF-8'); ?>'>
                                                    <?= htmlspecialchars($rewriteStatusString, ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?= $lang['user_url'] ?></td>
                                                <td style='color: <?= htmlspecialchars($userFriendlyUrlStatusColor, ENT_QUOTES, 'UTF-8'); ?>'>
                                                    <?= htmlspecialchars($userFriendlyUrlStatusString, ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card-footer">

                                    <form action="config-check" method="post" class="border-line">
                                        <input type="hidden" name="count" value="<?php echo $errorCount; ?>" />

                                        <button class="btn btn-primary float-right" type="submit" <?php echo $errorCount > 0 ? 'disabled' : ''; ?>>
                                            <?= $lang['continue'] ?>&nbsp;
                                            <i class="fas fa-arrow-right continue"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <footer class="main-footer">

        <div class="float-right d-none d-sm-inline">Agora Invoicing <?php echo $config['version']; ?></div>

        <strong>Copyright © 2025 <a href="javascript:;">Ladybird Web Solution Pvt Ltd.</a>.</strong> All rights reserved.
    </footer>
</div>

<script src="./admin/js/jquery.min.js"></script>
<script src="./admin/js/bootstrap.bundle.min.js"></script>
<script src="./admin/js/adminlte.min.js"></script>
<script src="./admin/js/bs-stepper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>


<script type="module">
    var body = document.body;
    // var currentDir = body.getAttribute('dir');


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
                console.log('Current language:', currentLanguage);
                if (currentLanguage === 'ar') {
                    $('head').append('<link href="admin/css-1/probe-rtl.css" rel="stylesheet" type="text/css" />');
                    $('head').append('<link href="admin/css-1/adminlte-rtl.css" rel="stylesheet" type="text/css" />');
                    $('head').append('<link href="admin/css-1/bs-stepper-rtl.css" rel="stylesheet" type="text/css" />');
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
                    $('head').append('<link href="admin/css-1/adminlte.min.css" rel="stylesheet" type="text/css" />');
                    $('head').append('<link href="admin/css-1/bs-stepper.css" rel="stylesheet" type="text/css" />');
                    $('head').append('<link href="admin/css-1/probe.css" rel="stylesheet" type="text/css" />');
                    const arrowElements = document.getElementsByClassName('fas fa-arrow-left');
                    for (let i = 0; i < arrowElements.length; i++) {
                        arrowElements[i].className = 'fas fa-arrow-right';
                    }
                    const setClassName = (elements, className) => {
                        Array.from(elements).forEach(element => {
                            element.className = className;
                        });
                    };

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


        // defaultStyles.forEach(function(link) {
        //     link.disabled = false;
        // });
        // rtlStyles.forEach(function(link) {
        //     link.disabled = true;
        // });
    }
    $('.toggle-password').click(function() {
        const input = $('#admin_password');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    $('.toggle-confirm-password').click(function() {
        const input = $('#admin_confirm_password');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

</script>
</body>
</html>