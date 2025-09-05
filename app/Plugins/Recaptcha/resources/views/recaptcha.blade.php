<?php
$recaptcha = \App\Plugins\Recaptcha\Model\RecaptchaSetting::first();

$statusSettings = \App\Model\Common\StatusSetting::first()->value('recaptcha_status');

$captchaVersion = $recaptcha->captcha_version ?? 'v3';
$defaultMode = match($captchaVersion) {
    'v2_checkbox' => 'v2',
    'v2_invisible' => 'v2-invisible',
    'v3' => 'v3',
    default => 'v3'
};

$v2SiteKey = $recaptcha->v2_site_key ?? '';
$v3SiteKey = $recaptcha->v3_site_key ?? '';
$theme = ($defaultMode == 'v2' ||   ($defaultMode === 'v3' && $recaptcha->failover_action === 'v2_checkbox')) ? $recaptcha->theme : 'light';
$fallback = ($defaultMode === 'v3' && $recaptcha->failover_action ?? 'none' === 'v2_checkbox');
$size = ($recaptcha->size === 'compact') ? 'compact' : 'normal';
$position = $recaptcha->badge_position ?? 'bottomright';

// For invisible modes, force size to 'invisible'
$actualSize = ($defaultMode === 'v2-invisible') ? 'invisible' : $size;

// Get the current application locale, default to 'en'
$recaptchaLang = app()->getLocale() ?? 'en';

$validationErrorMessage = __('recaptcha::recaptcha.captcha_message');
?>
<script>
    {!! file_get_contents(app_path('Plugins/Recaptcha/resources/assets/js/recaptcha.js')) !!}

    RecaptchaManager.setGlobalConfig({
        v2SiteKey: @json($v2SiteKey),
        v3SiteKey: @json($v3SiteKey),
        disabled: @json(!$statusSettings),
        defaultMode: @json($defaultMode),
        theme: @json($theme),
        size: @json($actualSize),
        badge: @json($position),
        isolated: false,
        fallback: @json($fallback),
        debug: false,
        lang: @json($recaptchaLang),
        validationErrorMessage: @json($validationErrorMessage)
    });
</script>