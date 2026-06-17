<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Impostazioni reCAPTCHA',
    'captcha_configuration' => 'Configurazione reCAPTCHA',
    'captcha_version' => 'Versione reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisibile',
    'recaptcha_v2_checkbox' => 'Casella di controllo reCAPTCHA v2',
    'select_captcha_type' => 'Seleziona quale versione di reCAPTCHA utilizzare',
    'failover_action' => 'Azione di failover',
    'none' => 'Nessuna',
    'fallback_v2_checkbox' => 'Fallback alla casella di controllo di reCAPTCHA v2',
    'action_if_captcha_fails' => 'Azione da intraprendere in caso di errore di reCAPTCHA',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Impostazioni di reCAPTCHA v3',
    'v3_site_key' => 'Chiave del sito v3',
    'enter_v3_site_key' => 'Inserisci la tua chiave del sito reCAPTCHA v3',
    'v3_secret_key' => 'Chiave segreta v3',
    'enter_v3_secret_key' => 'Inserisci la tua chiave segreta reCAPTCHA v3',
    'v3_score_threshold' => 'Soglia di punteggio v3',
    'v3_score_hint' => 'Valore compreso tra 0.0 e 1.0 (più alto è, meglio è)',
    'v3_preview' => 'Anteprima v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Impostazioni di reCAPTCHA v2',
    'v2_site_key' => 'Chiave del sito v2',
    'enter_v2_site_key' => 'Inserisci la tua chiave del sito reCAPTCHA v2',
    'v2_secret_key' => 'Chiave segreta v2',
    'enter_v2_secret_key' => 'Inserisci la tua chiave segreta reCAPTCHA v2',
    'v2_preview' => 'Anteprima v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Aspetto',
    'theme' => 'Tema',
    'theme_light' => 'Chiaro',
    'theme_dark' => 'Scuro',
    'size' => 'Dimensioni',
    'size_normal' => 'Normale',
    'size_compact' => 'Compatto',
    'badge_position' => 'Posizione del badge',
    'badge_bottomright' => 'In basso a destra',
    'badge_bottomleft' => 'In basso a sinistra',
    'badge_inline' => 'In linea',

    /*
    * Common
    */
    'save' => 'Salva',
    'saving' => 'Salvataggio',
    'home' => 'Home',
    'settings' => 'Impostazioni',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Impostazioni reCAPTCHA aggiornate con successo!',

    /*
    * Error messages
    */
    'captcha_message' => 'Verifica reCAPTCHA non riuscita. Riprova.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Questo campo è obbligatorio quando la condizione è soddisfatta',
    'select_captcha_version' => 'Seleziona una versione di reCAPTCHA',
    'v3_site_key_required' => 'La chiave del sito reCAPTCHA v3 è obbligatoria',
    'v3_secret_key_required' => 'La chiave segreta di reCAPTCHA v3 è obbligatoria',
    'v2_site_key_required' => 'La chiave del sito reCAPTCHA v2 è obbligatoria',
    'v2_secret_key_required' => 'La chiave segreta di reCAPTCHA v2 è obbligatoria',
    'valid_recaptcha_site_key' => 'Inserisci una chiave del sito reCAPTCHA valida',
    'valid_recaptcha_secret_key' => 'Inserisci una chiave segreta reCAPTCHA valida',
    'score_threshold_required' => 'La soglia di punteggio è richiesta per reCAPTCHA v3',
    'valid_number' => 'Inserisci un numero valido',
    'complete_recaptcha_v3' => 'Impossibile generare il token reCAPTCHA. Verifica che la chiave del sito sia configurata correttamente e valida.',
    'failed_generate_v3_token' => 'Impossibile generare il token reCAPTCHA. Verifica che la chiave del sito sia configurata correttamente e valida.',
    'complete_recaptcha_v2' => 'Completa il reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Impossibile generare il token reCAPTCHA v2.',
    'settings_saved' => 'Impostazioni salvate.',
    'failed_save_settings' => 'Impossibile salvare le impostazioni. Riprova.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'La chiave segreta o il token di risposta non sono validi',
    'captcha_verification_failed' => 'Verifica reCAPTCHA non riuscita (mancata corrispondenza punteggio/azione/nome host)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'La versione di reCAPTCHA è obbligatoria',
    'captcha_version_in' => 'La versione di reCAPTCHA selezionata non è valida',
    'failover_action_required' => 'L\'azione di failover è obbligatoria',
    'failover_action_in' => 'L\'azione di failover selezionata non è valida',
    'score_threshold_numeric' => 'La soglia di punteggio deve essere un numero',
    'score_threshold_min' => 'La soglia di punteggio deve essere almeno 0',
    'score_threshold_max' => 'La soglia di punteggio non deve essere maggiore di 1',
    'theme_required' => 'Il tema è obbligatorio',
    'theme_in' => 'Il tema selezionato non è valido',
    'size_required' => 'Le dimensioni sono obbligatorie',
    'size_in' => 'Le dimensioni selezionate non sono valide',
    'badge_position_required' => 'La posizione del badge è obbligatoria',
    'badge_position_in' => 'La posizione del badge selezionata non è valida',
];
