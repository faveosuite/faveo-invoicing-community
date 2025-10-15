<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'reCAPTCHA-innstillinger',
    'captcha_configuration' => 'reCAPTCHA-konfigurasjon',
    'captcha_version' => 'reCAPTCHA-versjon',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 usynlig',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 avmerkingsboks',
    'select_captcha_type' => 'Velg hvilken versjon av reCAPTCHA som skal brukes',
    'failover_action' => 'Failover-handling',
    'none' => 'Ingen',
    'fallback_v2_checkbox' => 'Tilbakefall til reCAPTCHA v2-avmerkingsboks',
    'action_if_captcha_fails' => 'Handling som skal utføres hvis reCAPTCHA mislykkes',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3-innstillinger',
    'v3_site_key' => 'v3-nettstedsnøkkel',
    'enter_v3_site_key' => 'Skriv inn din reCAPTCHA v3-nettstedsnøkkel',
    'v3_secret_key' => 'v3-hemmelig nøkkel',
    'enter_v3_secret_key' => 'Skriv inn din reCAPTCHA v3-hemmelige nøkkel',
    'v3_score_threshold' => 'v3-poengterskel',
    'v3_score_hint' => 'Verdi mellom 0,0 og 1,0 (høyere er bedre)',
    'v3_preview' => 'v3-forhåndsvisning',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2-innstillinger',
    'v2_site_key' => 'v2-nettstedsnøkkel',
    'enter_v2_site_key' => 'Skriv inn din reCAPTCHA v2-nettstedsnøkkel',
    'v2_secret_key' => 'v2-hemmelig nøkkel',
    'enter_v2_secret_key' => 'Skriv inn din reCAPTCHA v2-hemmelige nøkkel',
    'v2_preview' => 'v2-forhåndsvisning',

    /*
    * Appearance
    */
    'appearance_messages' => 'Utseende',
    'theme' => 'Tema',
    'theme_light' => 'Lyst',
    'theme_dark' => 'Mørkt',
    'size' => 'Størrelse',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Merkeposisjon',
    'badge_bottomright' => 'Nede til høyre',
    'badge_bottomleft' => 'Nede til venstre',
    'badge_inline' => 'Innebygd',

    /*
    * Common
    */
    'save' => 'Lagre',
    'saving' => 'Lagrer',
    'home' => 'Hjem',
    'settings' => 'Innstillinger',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA-innstillingene ble oppdatert!',

    /*
    * Error messages
    */
    'captcha_message' => 'reCAPTCHA-verifisering mislyktes. Prøv på nytt.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Dette feltet er obligatorisk når betingelsen er oppfylt',
    'select_captcha_version' => 'Velg en reCAPTCHA-versjon',
    'v3_site_key_required' => 'reCAPTCHA v3-nettstedsnøkkel er obligatorisk',
    'v3_secret_key_required' => 'reCAPTCHA v3-hemmelig nøkkel er obligatorisk',
    'v2_site_key_required' => 'reCAPTCHA v2-nettstedsnøkkel er obligatorisk',
    'v2_secret_key_required' => 'reCAPTCHA v2-hemmelig nøkkel er obligatorisk',
    'valid_recaptcha_site_key' => 'Skriv inn en gyldig reCAPTCHA-nettstedsnøkkel',
    'valid_recaptcha_secret_key' => 'Skriv inn en gyldig reCAPTCHA-hemmelig nøkkel',
    'score_threshold_required' => 'Poengterskel er obligatorisk for reCAPTCHA v3',
    'valid_number' => 'Skriv inn et gyldig tall',
    'complete_recaptcha_v3' => 'Kunne ikke generere reCAPTCHA-token. Kontroller at nettstedsnøkkelen er riktig konfigurert og gyldig.',
    'failed_generate_v3_token' => 'Kunne ikke generere reCAPTCHA-token. Kontroller at nettstedsnøkkelen er riktig konfigurert og gyldig.',
    'complete_recaptcha_v2' => 'Fullfør reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Kunne ikke generere reCAPTCHA v2-token.',
    'settings_saved' => 'Innstillingene er lagret.',
    'failed_save_settings' => 'Kunne ikke lagre innstillingene. Prøv på nytt.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Hemmelig nøkkel eller responstoken er ugyldig',
    'captcha_verification_failed' => 'reCAPTCHA-verifisering mislyktes (poengsum/handling/vertsnavn stemmer ikke overens)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'reCAPTCHA-versjon er obligatorisk',
    'captcha_version_in' => 'Valgt reCAPTCHA-versjon er ugyldig',
    'failover_action_required' => 'Failover-handling er obligatorisk',
    'failover_action_in' => 'Valgt failover-handling er ugyldig',
    'score_threshold_numeric' => 'Poengterskelen må være et tall',
    'score_threshold_min' => 'Poengterskelen må være minst 0',
    'score_threshold_max' => 'Poengterskelen må ikke være større enn 1',
    'theme_required' => 'Tema er obligatorisk',
    'theme_in' => 'Valgt tema er ugyldig',
    'size_required' => 'Størrelse er obligatorisk',
    'size_in' => 'Valgt størrelse er ugyldig',
    'badge_position_required' => 'Merkeposisjon er obligatorisk',
    'badge_position_in' => 'Valgt merkeposisjon er ugyldig',
];
