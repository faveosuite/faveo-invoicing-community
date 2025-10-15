<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Postavke reCAPTCHA',
    'captcha_configuration' => 'Konfiguracija reCAPTCHA',
    'captcha_version' => 'Verzija reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Nevidljiva',
    'recaptcha_v2_checkbox' => 'Potvrdni okvir reCAPTCHA v2',
    'select_captcha_type' => 'Odaberite koju verziju reCAPTCHA želite koristiti',
    'failover_action' => 'Radnja u slučaju prebacivanja na rezervni sistem',
    'none' => 'Nijedan',
    'fallback_v2_checkbox' => 'Vraćanje na potvrdni okvir reCAPTCHA v2',
    'action_if_captcha_fails' => 'Radnja koju treba poduzeti ako reCAPTCHA ne uspije',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Postavke reCAPTCHA v3',
    'v3_site_key' => 'Ključ web-lokacije v3',
    'enter_v3_site_key' => 'Unesite ključ svoje web-lokacije reCAPTCHA v3',
    'v3_secret_key' => 'Tajni ključ v3',
    'enter_v3_secret_key' => 'Unesite svoj tajni ključ reCAPTCHA v3',
    'v3_score_threshold' => 'Prag bodova v3',
    'v3_score_hint' => 'Vrijednost između 0,0 i 1,0 (veća je bolja)',
    'v3_preview' => 'Pregled v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Postavke reCAPTCHA v2',
    'v2_site_key' => 'Ključ web-lokacije v2',
    'enter_v2_site_key' => 'Unesite ključ svoje web-lokacije reCAPTCHA v2',
    'v2_secret_key' => 'Tajni ključ v2',
    'enter_v2_secret_key' => 'Unesite svoj tajni ključ reCAPTCHA v2',
    'v2_preview' => 'Pregled v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Izgled',
    'theme' => 'Tema',
    'theme_light' => 'Svijetla',
    'theme_dark' => 'Tamna',
    'size' => 'Veličina',
    'size_normal' => 'Normalna',
    'size_compact' => 'Kompaktna',
    'badge_position' => 'Položaj značke',
    'badge_bottomright' => 'Dolje desno',
    'badge_bottomleft' => 'Dolje lijevo',
    'badge_inline' => 'U retku',

    /*
    * Common
    */
    'save' => 'Spremi',
    'saving' => 'Spremanje',
    'home' => 'Početna',
    'settings' => 'Postavke',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Postavke reCAPTCHA uspješno su ažurirane!',

    /*
    * Error messages
    */
    'captcha_message' => 'Provjera reCAPTCHA nije uspjela. Pokušajte ponovo.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Ovo je polje obavezno kada je uvjet ispunjen',
    'select_captcha_version' => 'Odaberite verziju reCAPTCHA',
    'v3_site_key_required' => 'Potreban je ključ web-lokacije reCAPTCHA v3',
    'v3_secret_key_required' => 'Potreban je tajni ključ reCAPTCHA v3',
    'v2_site_key_required' => 'Potreban je ključ web-lokacije reCAPTCHA v2',
    'v2_secret_key_required' => 'Potreban je tajni ključ reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'Unesite važeći ključ web-lokacije reCAPTCHA',
    'valid_recaptcha_secret_key' => 'Unesite važeći tajni ključ reCAPTCHA',
    'score_threshold_required' => 'Prag bodova je potreban za reCAPTCHA v3',
    'valid_number' => 'Unesite važeći broj',
    'complete_recaptcha_v3' => 'Generiranje tokena reCAPTCHA nije uspjelo. Provjerite je li ključ web-lokacije ispravno konfiguriran i važeći.',
    'failed_generate_v3_token' => 'Generiranje tokena reCAPTCHA nije uspjelo. Provjerite je li ključ web-lokacije ispravno konfiguriran i važeći.',
    'complete_recaptcha_v2' => 'Dovršite reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Generiranje tokena reCAPTCHA v2 nije uspjelo.',
    'settings_saved' => 'Postavke su spremljene.',
    'failed_save_settings' => 'Spremanje postavki nije uspjelo. Pokušajte ponovo.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Tajni ključ ili token odgovora nisu važeći',
    'captcha_verification_failed' => 'Provjera reCAPTCHA nije uspjela (nepodudaranje bodova/radnje/naziva hosta)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'Potrebna je verzija reCAPTCHA',
    'captcha_version_in' => 'Odabrana verzija reCAPTCHA nije važeća',
    'failover_action_required' => 'Potrebna je radnja u slučaju prebacivanja na rezervni sistem',
    'failover_action_in' => 'Odabrana radnja u slučaju prebacivanja na rezervni sistem nije važeća',
    'score_threshold_numeric' => 'Prag bodova mora biti broj',
    'score_threshold_min' => 'Prag bodova mora biti najmanje 0',
    'score_threshold_max' => 'Prag bodova ne smije biti veći od 1',
    'theme_required' => 'Potrebna je tema',
    'theme_in' => 'Odabrana tema nije važeća',
    'size_required' => 'Potrebna je veličina',
    'size_in' => 'Odabrana veličina nije važeća',
    'badge_position_required' => 'Potreban je položaj značke',
    'badge_position_in' => 'Odabrani položaj značke nije važeći',
];
