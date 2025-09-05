<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Captcha Einstellungen',
    'captcha_configuration' => 'Captcha Konfiguration',
    'captcha_version' => 'Captcha Version',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Unsichtbar',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Checkbox',
    'select_captcha_type' => 'Wählen Sie aus, welche Version von reCAPTCHA verwendet werden soll',
    'failover_action' => 'Ausweichaktion',
    'none' => 'Keine',
    'fallback_v2_checkbox' => 'Fallback zu reCAPTCHA v2 Checkbox',
    'action_if_captcha_fails' => 'Aktion, wenn reCAPTCHA fehlschlägt',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 Einstellungen',
    'v3_site_key' => 'v3 Site Key',
    'enter_v3_site_key' => 'Geben Sie Ihren reCAPTCHA v3 Site Key ein',
    'v3_secret_key' => 'v3 Secret Key',
    'enter_v3_secret_key' => 'Geben Sie Ihren reCAPTCHA v3 Secret Key ein',
    'v3_score_threshold' => 'v3 Score Schwellenwert',
    'v3_score_hint' => 'Wert zwischen 0.0 und 1.0 (höher ist besser)',
    'v3_preview' => 'v3 Vorschau',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 Einstellungen',
    'v2_site_key' => 'v2 Site Key',
    'enter_v2_site_key' => 'Geben Sie Ihren reCAPTCHA v2 Site Key ein',
    'v2_secret_key' => 'v2 Secret Key',
    'enter_v2_secret_key' => 'Geben Sie Ihren reCAPTCHA v2 Secret Key ein',
    'v2_preview' => 'v2 Vorschau',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Erscheinungsbild & Nachrichten',
    'theme' => 'Thema',
    'theme_light' => 'Hell',
    'theme_dark' => 'Dunkel',
    'size' => 'Größe',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Abzeichen Position',
    'badge_bottomright' => 'Unten rechts',
    'badge_bottomleft' => 'Unten links',
    'badge_inline' => 'Inline',
    'error_message' => 'Fehlermeldung',
    
    /*
    * Common
    */
    'save' => 'Speichern',
    'saving' => 'Speichern',
    'home' => 'Startseite',
    'settings' => 'Einstellungen',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA-Einstellungen erfolgreich aktualisiert!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'Captcha-Überprüfung fehlgeschlagen. Bitte versuchen Sie es erneut.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Dieses Feld ist erforderlich, wenn die Bedingung erfüllt ist',
    'select_captcha_version' => 'Bitte wählen Sie eine Captcha-Version',
    'v3_site_key_required' => 'reCAPTCHA v3 Site Key ist erforderlich',
    'v3_secret_key_required' => 'reCAPTCHA v3 Secret Key ist erforderlich',
    'v2_site_key_required' => 'reCAPTCHA v2 Site Key ist erforderlich',
    'v2_secret_key_required' => 'reCAPTCHA v2 Secret Key ist erforderlich',
    'valid_recaptcha_site_key' => 'Bitte geben Sie einen gültigen reCAPTCHA Site Key ein',
    'valid_recaptcha_secret_key' => 'Bitte geben Sie einen gültigen reCAPTCHA Secret Key ein',
    'score_threshold_required' => 'Score-Schwelle ist für reCAPTCHA v3 erforderlich',
    'valid_number' => 'Bitte geben Sie eine gültige Zahl ein',
    'complete_recaptcha_v3' => 'Bitte vervollständigen Sie das reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Fehler beim Generieren des reCAPTCHA v3 Tokens.',
    'complete_recaptcha_v2' => 'Bitte vervollständigen Sie das reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Fehler beim Generieren des reCAPTCHA v2 Tokens.',
    'settings_saved' => 'Einstellungen gespeichert.',
    'failed_save_settings' => 'Fehler beim Speichern der Einstellungen. Bitte versuchen Sie es erneut.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Geheimer Schlüssel oder Antwort-Token ist ungültig',
    'captcha_verification_failed' => 'Captcha-Überprüfung fehlgeschlagen (Score/Aktion/Hostname stimmen nicht überein)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'Captcha-Version ist erforderlich',
    'captcha_version_in' => 'Ausgewählte Captcha-Version ist ungültig',
    'failover_action_required' => 'Ausweichaktion ist erforderlich',
    'failover_action_in' => 'Ausgewählte Ausweichaktion ist ungültig',
    'score_threshold_numeric' => 'Score-Schwelle muss eine Zahl sein',
    'score_threshold_min' => 'Score-Schwelle muss mindestens 0 betragen',
    'score_threshold_max' => 'Score-Schwelle darf nicht größer als 1 sein',
    'error_message_required' => 'Fehlermeldung ist erforderlich',
    'error_message_max' => 'Fehlermeldung darf 255 Zeichen nicht überschreiten',
    'theme_required' => 'Thema ist erforderlich',
    'theme_in' => 'Ausgewähltes Thema ist ungültig',
    'size_required' => 'Größe ist erforderlich',
    'size_in' => 'Ausgewählte Größe ist ungültig',
    'badge_position_required' => 'Abzeichenposition ist erforderlich',
    'badge_position_in' => 'Ausgewählte Abzeichenposition ist ungültig',
];