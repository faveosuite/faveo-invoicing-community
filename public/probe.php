<?php
require __DIR__ . '/../bootstrap/autoload.php';
$config = require_once '../config/app.php';

use App\Http\Controllers\BillingInstaller\BillingDependencyController;

require_once dirname(__DIR__, 1) . '/app/Http/helpers.php';

$passwordMatched = false;
$showError = false;
$env = '../.env';
$envFound = is_file($env);
if ($envFound) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}
if (isset($_POST['submit'])) {

    $probePhrase = env('PROBE_PASS_PHRASE', '   ');

    $input = $_POST['passPhrase'];
    if ($input != $probePhrase) {
        $showError = true;
    } else {
        $passwordMatched = true;
    }
}

$currentLang = 'en';
function fetchLang(): array
{
    $locale = $_ENV['APP_LOCALE'] ?? 'en';
    $langPath = dirname(__DIR__) . '/lang/' . $locale . '/installer_messages.php';

    if (!is_file($langPath)) {
        $locale = 'en';
        $langPath = dirname(__DIR__) . '/lang/en/installer_messages.php';
    }

    return [
            'lang' => require $langPath,
            'currentLang' => $locale,
    ];
}

$fetchLang = fetchLang();
$lang = $fetchLang['lang'];
$currentLang = $fetchLang['currentLang'];
$isRtl    = in_array($currentLang, ['ar', 'he']);
$baseUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? '') . dirname($_SERVER['SCRIPT_NAME'] ?? '');

$localeMap = [
    'ar' => 'ae', 'bsn' => 'bs', 'de' => 'de', 'en' => 'us', 'en-gb' => 'gb',
    'es' => 'es', 'fr' => 'fr', 'id' => 'id', 'it' => 'it', 'kr' => 'kr',
    'mt' => 'mt', 'nl' => 'nl', 'no' => 'no', 'pt' => 'pt', 'ru' => 'ru',
    'vi' => 'vn', 'zh-hans' => 'cn', 'zh-hant' => 'cn', 'ja' => 'jp',
    'ta' => 'in', 'hi' => 'in', 'he' => 'il', 'tr' => 'tr',
];
$languages = array_map('basename', glob(dirname(__DIR__) . '/lang/*', GLOB_ONLYDIR));
$langConfig = require dirname(__DIR__) . '/config/languages.php';
?>
<!Doctype html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $lang['title'] ?></title>

    <link rel="shortcut icon" href="./images/faveo.png" type="image/x-icon"/>
    <link rel="stylesheet"
          href="./themes/default/common/bootstrap/css/<?= $isRtl ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css' ?>">
    <link rel="stylesheet" href="./themes/default/common/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="./themes/default/common/flag-icons/css/flag-icons.min.css">
    <link rel="stylesheet"
          href="./themes/default/admin/adminlte/css/<?= $isRtl ? 'adminlte.rtl.min.css' : 'adminlte.min.css' ?>">

    <style>
        .wizard-steps { counter-reset: step; list-style: none; margin: 0; padding: 0; display: flex; justify-content: space-around; align-items: flex-start; }
        .wizard-steps li { flex: 1; position: relative; padding: 0 0.75rem; margin: 0; text-align: center; color: var(--bs-secondary-color); font-size: 0.875rem; }
        .wizard-steps li::before { counter-increment: step; content: counter(step); display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; margin: 0 auto 0.5rem; border-radius: 50%; background: #adb5bd; border: 2px solid #adb5bd; color: #fff; font-weight: 600; font-size: 1.1rem; position: relative; z-index: 1; }
        .wizard-steps li:not(:last-child)::after { content: ''; position: absolute; top: 1.5rem; left: 50%; width: 100%; height: 2px; background: var(--bs-border-color); z-index: 0; }
        .wizard-steps li.active { color: var(--bs-primary); font-weight: 600; }
        .wizard-steps li.active::before { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }
        .wizard-steps li.completed::before { background: var(--bs-success); border-color: var(--bs-success); color: #fff; content: '\2713'; }
        .wizard-steps li.completed:not(:last-child)::after { background: var(--bs-success); }
        .card .table td, .card .table th { padding: 0.9rem 1rem; font-size: 0.8rem; }

.cursor-default {
            cursor: default !important;
        }

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

        .form-control.is-invalid {
            background-image: none !important;
        }

        #progress {
            width: 93%;
            margin-left: 54px;
        }

    </style>
</head>

<body class="layout-fixed bg-body-tertiary">

<div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container">
            <div class="col-xl-10 mx-auto d-flex align-items-center">
            <a href="javascript:;" class="navbar-brand">
                <img src="./images/agora-invoicing.png" alt="Agora Logo" style="height:50px;">
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

                        $currentLanguage = $currentLang;
                        $flagClass = 'fi fi-' . strtolower($localeMap[$currentLanguage] ?? 'us');
                        ?>
                        <i id="flagIcon" class="<?= $flagClass ?>"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" id="language-dropdown">
                        <?php foreach ($languages as $locale):
                            $flagCode = $localeMap[$locale] ?? $locale;
                            $name = $langConfig[$locale][0] ?? $locale;
                        ?>
                        <a href="javascript:;" class="dropdown-item<?= $locale === $currentLang ? ' active' : '' ?>"
                           data-locale="<?= htmlspecialchars($locale) ?>">
                            <i class="fi fi-<?= strtolower($flagCode) ?> me-2"></i>
                            <?= htmlspecialchars($name) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </li>
            </ul>
            </div><!-- /.col-xl-10 -->
        </div><!-- /.container -->
    </nav>

    <main class="app-main">
    <?php
    if ($envFound && !$passwordMatched) {
        ?>
        <div class="app-content">
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
                                                   placeholder="<?= $lang['enter_magic_phrase'] ?>"
                                                   value="<?= isset($_POST['passPhrase']) ? htmlspecialchars($_POST['passPhrase']) : '' ?>">

                                            <?php if (isset($showError) && $showError) { ?>
                                                <span class="error invalid-feedback"><?= $lang['magic_phrase_not_work'] ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">

                                    <button class="btn btn-primary float-end" name="submit" id="magic-phrase-submit">
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
            document.getElementById("magic-phrase-submit").addEventListener("click", function (event) {
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
        <div class="app-content">
            <div class="container py-3">

                <?php
                function colorClass(string $color): string {
                    return match ($color) {
                        'green'  => 'text-success',
                        'red'    => 'text-danger',
                        default  => 'text-warning',
                    };
                }
                $errorCount = 0;
                $basePath = substr(__DIR__, 0, -6);
                $billingController = new BillingDependencyController('probe');
                $dirDetails = $billingController->validateDirectory($basePath, $errorCount);
                $reqDetails = (new BillingDependencyController('probe'))->validateRequisites($errorCount);
                $extDetails = (new BillingDependencyController('probe'))->validatePHPExtensions($errorCount);
                $phpIniFile = php_ini_loaded_file();
                $extString = str_replace(
                    [':php_ini_file', ':extensionName', ':url'],
                    [$phpIniFile, $extDetails[0]['extensionName'] ?? '', 'https://support.faveohelpdesk.com/show/how-to-enable-required-php-extension-on-different-servers-for-faveo-installation'],
                    $lang['extension_not_enabled']
                );
                function getLicenseUrl(): string {
                    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                    $url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    return str_replace('probe.php', 'db-setup', $url);
                }
                function checkUserFriendlyUrl(): ?bool {
                    if (!function_exists('curl_init')) return null;
                    try {
                        $ch = curl_init(getLicenseUrl());
                        curl_setopt_array($ch, [CURLOPT_HEADER => true, CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 10]);
                        curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        return $code != 404;
                    } catch (Exception $e) { return null; }
                }
                $redirect = function_exists('apache_get_modules') ? (int) in_array('mod_rewrite', apache_get_modules()) : 2;
                $rewriteClass  = $redirect === 2 ? 'text-warning' : ($redirect ? 'text-success' : 'text-danger');
                $rewriteString = $redirect === 2 ? 'Unable to detect' : ($redirect ? 'Enabled' : 'OFF');
                if (!$redirect && $redirect !== 2) $errorCount++;
                $userFriendlyUrl = checkUserFriendlyUrl();
                $urlClass  = $userFriendlyUrl === false ? 'text-danger' : ($userFriendlyUrl === true ? 'text-success' : 'text-warning');
                $urlString = $userFriendlyUrl === false ? $lang['off_apache'] : ($userFriendlyUrl === true ? 'Enabled' : 'Unable to detect');
                if ($userFriendlyUrl === false) $errorCount++;
                ?>

                <div class="col-xl-10 mx-auto">
                <ol class="wizard-steps mb-4">
                    <li class="active" id="server"><?= $lang['server_requirements'] ?></li>
                    <li id="database"><?= $lang['database_setup'] ?></li>
                    <li id="start"><?= $lang['getting_started'] ?></li>
                    <li id="final"><?= $lang['final'] ?></li>
                </ol>

                <div id="alert-container"></div>

                <div class="card">
                    <div class="card-body">

                        <p class="text-center lead fw-bold"><?= $lang['server_requirements'] ?></p>

                        <table class="table table-bordered mb-4 small">
                            <thead class="table-light">
                                <tr><th style="width:50%;"><?= $lang['directory'] ?></th><th><?= $lang['permissions'] ?></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($dirDetails as $item) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['extensionName'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="<?= colorClass($item['color']) ?>"><?= htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-bordered mb-4 small">
                            <thead class="table-light">
                                <tr><th style="width:50%;"><?= $lang['requisites'] ?></th><th><?= $lang['status'] ?></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($reqDetails as $detail) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($detail['extensionName'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="<?= colorClass($detail['color']) ?>"><?= $detail['connection'] ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-bordered mb-4 small">
                            <thead class="table-light">
                                <tr><th style="width:50%;"><?= $lang['php_extensions'] ?></th><th><?= $lang['status'] ?></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($extDetails as $item) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['extensionName'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="<?= $item['key'] === 'no-error' ? 'text-success' : 'text-warning' ?>"><?= $item['key'] === 'no-error' ? 'Enabled' : $extString ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-bordered small">
                            <thead class="table-light">
                                <tr><th style="width:50%;"><?= $lang['mod_rewrite'] ?></th><th><?= $lang['status'] ?></th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= $lang['rewrite_engine'] ?></td>
                                    <td class="<?= $rewriteClass ?>"><?= htmlspecialchars($rewriteString, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <tr>
                                    <td><?= $lang['user_url'] ?></td>
                                    <td class="<?= $urlClass ?>"><?= htmlspecialchars($urlString, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer">
                        <form action="config-check" method="post">
                            <input type="hidden" name="count" value="<?= $errorCount ?>"/>
                            <button class="btn btn-primary float-end" type="submit" <?= $errorCount > 0 ? 'disabled' : '' ?>>
                                <?= $lang['continue'] ?>&nbsp;<i class="fas fa-arrow-right continue"></i>
                            </button>
                        </form>
                    </div>
                </div>
                </div><!-- /.col -->

            </div>
        </div>
    <?php } ?>
    </main>

    <footer class="app-footer">

        <div class="float-end d-none d-sm-inline">Agora Invoicing <?php echo $config['version']; ?></div>

        <strong><?= $lang['copyright'] ?> © 2015 - <?= date('Y') ?> <span class="cursor-normal text-primary">Ladybird Web Solution Pvt Ltd.</span></strong> <?= $lang['powered_by'] ?>
        <strong><a href="https://www.faveohelpdesk.com/" target="_blank">Faveo</a></strong>
    </footer>
</div>

<script src="./themes/default/common/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="./themes/default/admin/adminlte/js/adminlte.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const localeMap = {
        'ar': 'ae',
        'bsn': 'bs',
        'de': 'de',
        'en': 'us',
        'en-gb': 'gb',
        'es': 'es',
        'fr': 'fr',
        'id': 'id',
        'it': 'it',
        'kr': 'kr',
        'mt': 'mt',
        'nl': 'nl',
        'no': 'no',
        'pt': 'pt',
        'ru': 'ru',
        'vi': 'vn',
        'zh-hans': 'cn',
        'zh-hant': 'cn',
        'ja': 'jp',
        'ta': 'in',
        'hi': 'in',
        'he': 'il',
        'tr': 'tr'
    };

    function updateLanguage(language, flagClass) {
        document.getElementById('flagIcon').className = flagClass;
        fetch('<?= $baseUrl ?>/update/language', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({language}),
        })
            .then(() => window.location.reload())
            .catch(err => console.error('Error updating language:', err));
    }

    document.addEventListener('click', function (e) {
        const item = e.target.closest('.dropdown-item');
        if (!item) return;
        const selectedLanguage = item.dataset.locale;
        const mappedLocale = localeMap[selectedLanguage] || selectedLanguage;
        updateLanguage(selectedLanguage, 'fi fi-' + mappedLocale.toLowerCase());
    });

    const currentLanguage = '<?php echo $currentLang; ?>';
    const setClassName = (elements, className) => Array.from(elements).forEach(el => {
        el.className = className;
    });
    if (currentLanguage === 'ar' || currentLanguage === 'he') {
        setClassName(document.getElementsByClassName('continue'), 'fas fa-arrow-left');
        setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-right');
    } else {
        setClassName(document.getElementsByClassName('continue'), 'continue fas fa-arrow-right');
        setClassName(document.getElementsByClassName('previous'), 'fas fa-arrow-left');
    }

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById('admin_password');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    });

    document.querySelectorAll('.toggle-confirm-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById('admin_confirm_password');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
    });
}); // DOMContentLoaded
</script>
</body>
</html>