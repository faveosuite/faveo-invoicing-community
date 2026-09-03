<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'reCAPTCHA-instellingen',
    'captcha_configuration' => 'reCAPTCHA-configuratie',
    'captcha_version' => 'reCAPTCHA-versie',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 onzichtbaar',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 selectievakje',
    'select_captcha_type' => 'Selecteer welke versie van reCAPTCHA u wilt gebruiken',
    'failover_action' => 'Failover-actie',
    'none' => 'Geen',
    'fallback_v2_checkbox' => 'Terugvallen op reCAPTCHA v2-selectievakje',
    'action_if_captcha_fails' => 'Actie die moet worden ondernomen als reCAPTCHA mislukt',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3-instellingen',
    'v3_site_key' => 'v3-sitesleutel',
    'enter_v3_site_key' => 'Voer uw reCAPTCHA v3-sitesleutel in',
    'v3_secret_key' => 'v3-geheime sleutel',
    'enter_v3_secret_key' => 'Voer uw reCAPTCHA v3-geheime sleutel in',
    'v3_score_threshold' => 'v3-scoredrempel',
    'v3_score_hint' => 'Waarde tussen 0,0 en 1,0 (hoger is beter)',
    'v3_preview' => 'v3-voorbeeld',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2-instellingen',
    'v2_site_key' => 'v2-sitesleutel',
    'enter_v2_site_key' => 'Voer uw reCAPTCHA v2-sitesleutel in',
    'v2_secret_key' => 'v2-geheime sleutel',
    'enter_v2_secret_key' => 'Voer uw reCAPTCHA v2-geheime sleutel in',
    'v2_preview' => 'v2-voorbeeld',

    /*
    * Appearance
    */
    'appearance_messages' => 'Uiterlijk',
    'theme' => 'Thema',
    'theme_light' => 'Licht',
    'theme_dark' => 'Donker',
    'size' => 'Grootte',
    'size_normal' => 'Normaal',
    'size_compact' => 'Compact',
    'badge_position' => 'Badgepositie',
    'badge_bottomright' => 'Rechtsonder',
    'badge_bottomleft' => 'Linksonder',
    'badge_inline' => 'Inline',

    /*
    * Common
    */
    'save' => 'Opslaan',
    'saving' => 'Opslaan',
    'home' => 'Home',
    'settings' => 'Instellingen',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA-instellingen succesvol bijgewerkt!',

    /*
    * Error messages
    */
    'captcha_message' => 'reCAPTCHA-verificatie mislukt. Probeer het opnieuw.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Dit veld is verplicht wanneer aan de voorwaarde is voldaan',
    'select_captcha_version' => 'Selecteer een reCAPTCHA-versie',
    'v3_site_key_required' => 'reCAPTCHA v3-sitesleutel is verplicht',
    'v3_secret_key_required' => 'reCAPTCHA v3-geheime sleutel is verplicht',
    'v2_site_key_required' => 'reCAPTCHA v2-sitesleutel is verplicht',
    'v2_secret_key_required' => 'reCAPTCHA v2-geheime sleutel is verplicht',
    'valid_recaptcha_site_key' => 'Voer een geldige reCAPTCHA-sitesleutel in',
    'valid_recaptcha_secret_key' => 'Voer een geldige reCAPTCHA-geheime sleutel in',
    'score_threshold_required' => 'Scoredrempel is vereist voor reCAPTCHA v3',
    'valid_number' => 'Voer een geldig nummer in',
    'complete_recaptcha_v3' => 'Kan reCAPTCHA-token niet genereren. Controleer of de sitesleutel correct is geconfigureerd en geldig is.',
    'failed_generate_v3_token' => 'Kan reCAPTCHA-token niet genereren. Controleer of de sitesleutel correct is geconfigureerd en geldig is.',
    'complete_recaptcha_v2' => 'Voltooi de reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Kan reCAPTCHA v2-token niet genereren.',
    'settings_saved' => 'Instellingen opgeslagen.',
    'failed_save_settings' => 'Kan instellingen niet opslaan. Probeer het opnieuw.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Geheime sleutel of responstoken is ongeldig',
    'captcha_verification_failed' => 'reCAPTCHA-verificatie mislukt (score/actie/hostnaam komen niet overeen)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'reCAPTCHA-versie is verplicht',
    'captcha_version_in' => 'Geselecteerde reCAPTCHA-versie is ongeldig',
    'failover_action_required' => 'Failover-actie is verplicht',
    'failover_action_in' => 'Geselecteerde failover-actie is ongeldig',
    'score_threshold_numeric' => 'Scoredrempel moet een getal zijn',
    'score_threshold_min' => 'Scoredrempel moet minimaal 0 zijn',
    'score_threshold_max' => 'Scoredrempel mag niet groter zijn dan 1',
    'theme_required' => 'Thema is verplicht',
    'theme_in' => 'Geselecteerd thema is ongeldig',
    'size_required' => 'Grootte is verplicht',
    'size_in' => 'Geselecteerde grootte is ongeldig',
    'badge_position_required' => 'Badgepositie is verplicht',
    'badge_position_in' => 'Geselecteerde badgepositie is ongeldig',
];
