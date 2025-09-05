<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Impostazioni Captcha',
    'captcha_configuration' => 'Configurazione Captcha',
    'captcha_version' => 'Versione Captcha',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisibile',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Casella di controllo',
    'select_captcha_type' => 'Seleziona la versione di reCAPTCHA da utilizzare',
    'failover_action' => 'Azione di Failover',
    'none' => 'Nessuna',
    'fallback_v2_checkbox' => 'Ritorna a reCAPTCHA v2 Casella di controllo',
    'action_if_captcha_fails' => 'Azione da intraprendere se reCAPTCHA fallisce',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Impostazioni reCAPTCHA v3',
    'v3_site_key' => 'Chiave del sito v3',
    'enter_v3_site_key' => 'Inserisci la chiave del sito reCAPTCHA v3',
    'v3_secret_key' => 'Chiave segreta v3',
    'enter_v3_secret_key' => 'Inserisci la chiave segreta reCAPTCHA v3',
    'v3_score_threshold' => 'Soglia del punteggio v3',
    'v3_score_hint' => 'Valore compreso tra 0.0 e 1.0 (più alto è meglio)',
    'v3_preview' => 'Anteprima v3',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Impostazioni reCAPTCHA v2',
    'v2_site_key' => 'Chiave del sito v2',
    'enter_v2_site_key' => 'Inserisci la chiave del sito reCAPTCHA v2',
    'v2_secret_key' => 'Chiave segreta v2',
    'enter_v2_secret_key' => 'Inserisci la chiave segreta reCAPTCHA v2',
    'v2_preview' => 'Anteprima v2',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Aspetto e messaggi',
    'theme' => 'Tema',
    'theme_light' => 'Chiaro',
    'theme_dark' => 'Scuro',
    'size' => 'Dimensione',
    'size_normal' => 'Normale',
    'size_compact' => 'Compatta',
    'badge_position' => 'Posizione del badge',
    'badge_bottomright' => 'In basso a destra',
    'badge_bottomleft' => 'In basso a sinistra',
    'badge_inline' => 'In linea',
    'error_message' => 'Messaggio di errore',
    
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
    'captcha_message' => 'Verifica captcha fallita. Riprova.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Questo campo è obbligatorio quando la condizione è soddisfatta',
    'select_captcha_version' => 'Seleziona una versione captcha',
    'v3_site_key_required' => 'Chiave del sito reCAPTCHA v3 obbligatoria',
    'v3_secret_key_required' => 'Chiave segreta reCAPTCHA v3 obbligatoria',
    'v2_site_key_required' => 'Chiave del sito reCAPTCHA v2 obbligatoria',
    'v2_secret_key_required' => 'Chiave segreta reCAPTCHA v2 obbligatoria',
    'valid_recaptcha_site_key' => 'Inserisci una chiave del sito reCAPTCHA valida',
    'valid_recaptcha_secret_key' => 'Inserisci una chiave segreta reCAPTCHA valida',
    'score_threshold_required' => 'La soglia del punteggio è obbligatoria per reCAPTCHA v3',
    'valid_number' => 'Inserisci un numero valido',
    'complete_recaptcha_v3' => 'Completa reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Impossibile generare il token reCAPTCHA v3.',
    'complete_recaptcha_v2' => 'Completa reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Impossibile generare il token reCAPTCHA v2.',
    'settings_saved' => 'Impostazioni salvate.',
    'failed_save_settings' => 'Impossibile salvare le impostazioni. Riprova.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Chiave segreta o token di risposta non valido',
    'captcha_verification_failed' => 'Verifica Captcha fallita (mancata corrispondenza punteggio/azione/nome host)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'La versione Captcha è obbligatoria',
    'captcha_version_in' => 'La versione Captcha selezionata non è valida',
    'failover_action_required' => 'L\'azione di failover è obbligatoria',
    'failover_action_in' => 'L\'azione di failover selezionata non è valida',
    'score_threshold_numeric' => 'La soglia del punteggio deve essere un numero',
    'score_threshold_min' => 'La soglia del punteggio deve essere almeno 0',
    'score_threshold_max' => 'La soglia del punteggio non deve superare 1',
    'error_message_required' => 'Il messaggio di errore è obbligatorio',
    'error_message_max' => 'Il messaggio di errore non deve superare 255 caratteri',
    'theme_required' => 'Il tema è obbligatorio',
    'theme_in' => 'Il tema selezionato non è valido',
    'size_required' => 'La dimensione è obbligatoria',
    'size_in' => 'La dimensione selezionata non è valida',
    'badge_position_required' => 'La posizione del badge è obbligatoria',
    'badge_position_in' => 'La posizione del badge selezionata non è valida',
];