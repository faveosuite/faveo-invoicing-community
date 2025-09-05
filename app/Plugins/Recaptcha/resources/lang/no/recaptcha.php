<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Captcha-innstillinger',
    'captcha_configuration' => 'Captcha-konfigurasjon',
    'captcha_version' => 'Captcha-versjon',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Usynlig',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Avkryssingsboks',
    'select_captcha_type' => 'Velg hvilken versjon av reCAPTCHA du vil bruke',
    'failover_action' => 'Failover-handling',
    'none' => 'Ingen',
    'fallback_v2_checkbox' => 'Fall tilbake til reCAPTCHA v2 Avkryssingsboks',
    'action_if_captcha_fails' => 'Handling som skal utføres hvis reCAPTCHA mislykkes',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3-innstillinger',
    'v3_site_key' => 'v3 Nøkkel for nettsted',
    'enter_v3_site_key' => 'Skriv inn din reCAPTCHA v3-nøkkel for nettstedet',
    'v3_secret_key' => 'v3 Hemmelig nøkkel',
    'enter_v3_secret_key' => 'Skriv inn din hemmelige reCAPTCHA v3-nøkkel',
    'v3_score_threshold' => 'v3 Poengterskel',
    'v3_score_hint' => 'Verdi mellom 0,0 og 1,0 (høyere er bedre)',
    'v3_preview' => 'v3 Forhåndsvisning',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2-innstillinger',
    'v2_site_key' => 'v2 Nøkkel for nettsted',
    'enter_v2_site_key' => 'Skriv inn din reCAPTCHA v2-nøkkel for nettstedet',
    'v2_secret_key' => 'v2 Hemmelig nøkkel',
    'enter_v2_secret_key' => 'Skriv inn din hemmelige reCAPTCHA v2-nøkkel',
    'v2_preview' => 'v2 Forhåndsvisning',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Utseende og meldinger',
    'theme' => 'Tema',
    'theme_light' => 'Lys',
    'theme_dark' => 'Mørk',
    'size' => 'Størrelse',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Posisjon for merke',
    'badge_bottomright' => 'Nederst til høyre',
    'badge_bottomleft' => 'Nederst til venstre',
    'badge_inline' => 'Inline',
    'error_message' => 'Feilmelding',
    
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
    'captcha_settings_updated' => 'reCAPTCHA-innstillinger oppdatert!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'Captcha-verifisering mislyktes. Vennligst prøv igjen.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Dette feltet er obligatorisk når betingelsen er oppfylt',
    'select_captcha_version' => 'Vennligst velg en captcha-versjon',
    'v3_site_key_required' => 'reCAPTCHA v3-nettstedsnøkkel er påkrevd',
    'v3_secret_key_required' => 'reCAPTCHA v3 hemmelig nøkkel er påkrevd',
    'v2_site_key_required' => 'reCAPTCHA v2-nettstedsnøkkel er påkrevd',
    'v2_secret_key_required' => 'reCAPTCHA v2 hemmelig nøkkel er påkrevd',
    'valid_recaptcha_site_key' => 'Vennligst skriv inn en gyldig reCAPTCHA-nettstedsnøkkel',
    'valid_recaptcha_secret_key' => 'Vennligst skriv inn en gyldig reCAPTCHA hemmelig nøkkel',
    'score_threshold_required' => 'Poengterskel er påkrevd for reCAPTCHA v3',
    'valid_number' => 'Vennligst skriv inn et gyldig tall',
    'complete_recaptcha_v3' => 'Vennligst fullfør reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Kunne ikke generere reCAPTCHA v3-token.',
    'complete_recaptcha_v2' => 'Vennligst fullfør reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Kunne ikke generere reCAPTCHA v2-token.',
    'settings_saved' => 'Innstillinger lagret.',
    'failed_save_settings' => 'Kunne ikke lagre innstillinger. Vennligst prøv igjen.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Hemmelig nøkkel eller svartoken er ugyldig',
    'captcha_verification_failed' => 'Captcha-verifisering mislyktes (poeng/Handling/vertsnavn stemmer ikke)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'Captcha-versjon er påkrevd',
    'captcha_version_in' => 'Valgt captcha-versjon er ugyldig',
    'failover_action_required' => 'Failover-handling er påkrevd',
    'failover_action_in' => 'Valgt failover-handling er ugyldig',
    'score_threshold_numeric' => 'Poengterskel må være et tall',
    'score_threshold_min' => 'Poengterskel må være minst 0',
    'score_threshold_max' => 'Poengterskel kan ikke være større enn 1',
    'error_message_required' => 'Feilmelding er påkrevd',
    'error_message_max' => 'Feilmelding kan ikke overstige 255 tegn',
    'theme_required' => 'Tema er påkrevd',
    'theme_in' => 'Valgt tema er ugyldig',
    'size_required' => 'Størrelse er påkrevd',
    'size_in' => 'Valgt størrelse er ugyldig',
    'badge_position_required' => 'Posisjon for merke er påkrevd',
    'badge_position_in' => 'Valgt posisjon for merke er ugyldig',
];