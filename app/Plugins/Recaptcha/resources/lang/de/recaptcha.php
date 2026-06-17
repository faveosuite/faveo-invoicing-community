<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'reCAPTCHA-Einstellungen',
    'captcha_configuration' => 'reCAPTCHA-Konfiguration',
    'captcha_version' => 'reCAPTCHA-Version',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 unsichtbar',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Kontrollkästchen',
    'select_captcha_type' => 'Wählen Sie aus, welche Version von reCAPTCHA verwendet werden soll',
    'failover_action' => 'Failover-Aktion',
    'none' => 'Keine',
    'fallback_v2_checkbox' => 'Fallback auf reCAPTCHA v2 Kontrollkästchen',
    'action_if_captcha_fails' => 'Aktion, die ausgeführt werden soll, wenn reCAPTCHA fehlschlägt',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3-Einstellungen',
    'v3_site_key' => 'v3-Site-Schlüssel',
    'enter_v3_site_key' => 'Geben Sie Ihren reCAPTCHA v3-Site-Schlüssel ein',
    'v3_secret_key' => 'v3-Geheimschlüssel',
    'enter_v3_secret_key' => 'Geben Sie Ihren reCAPTCHA v3-Geheimschlüssel ein',
    'v3_score_threshold' => 'v3-Score-Schwellenwert',
    'v3_score_hint' => 'Wert zwischen 0,0 und 1,0 (höher ist besser)',
    'v3_preview' => 'v3-Vorschau',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2-Einstellungen',
    'v2_site_key' => 'v2-Site-Schlüssel',
    'enter_v2_site_key' => 'Geben Sie Ihren reCAPTCHA v2-Site-Schlüssel ein',
    'v2_secret_key' => 'v2-Geheimschlüssel',
    'enter_v2_secret_key' => 'Geben Sie Ihren reCAPTCHA v2-Geheimschlüssel ein',
    'v2_preview' => 'v2-Vorschau',

    /*
    * Appearance
    */
    'appearance_messages' => 'Erscheinungsbild',
    'theme' => 'Thema',
    'theme_light' => 'Hell',
    'theme_dark' => 'Dunkel',
    'size' => 'Größe',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Abzeichenposition',
    'badge_bottomright' => 'Unten rechts',
    'badge_bottomleft' => 'Unten links',
    'badge_inline' => 'Inline',

    /*
    * Common
    */
    'save' => 'Speichern',
    'saving' => 'Wird gespeichert',
    'home' => 'Startseite',
    'settings' => 'Einstellungen',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA-Einstellungen erfolgreich aktualisiert!',

    /*
    * Error messages
    */
    'captcha_message' => 'reCAPTCHA-Überprüfung fehlgeschlagen. Bitte versuchen Sie es erneut.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Dieses Feld ist erforderlich, wenn die Bedingung erfüllt ist',
    'select_captcha_version' => 'Bitte wählen Sie eine reCAPTCHA-Version aus',
    'v3_site_key_required' => 'reCAPTCHA v3-Site-Schlüssel ist erforderlich',
    'v3_secret_key_required' => 'reCAPTCHA v3-Geheimschlüssel ist erforderlich',
    'v2_site_key_required' => 'reCAPTCHA v2-Site-Schlüssel ist erforderlich',
    'v2_secret_key_required' => 'reCAPTCHA v2-Geheimschlüssel ist erforderlich',
    'valid_recaptcha_site_key' => 'Bitte geben Sie einen gültigen reCAPTCHA-Site-Schlüssel ein',
    'valid_recaptcha_secret_key' => 'Bitte geben Sie einen gültigen reCAPTCHA-Geheimschlüssel ein',
    'score_threshold_required' => 'Score-Schwellenwert ist für reCAPTCHA v3 erforderlich',
    'valid_number' => 'Bitte geben Sie eine gültige Nummer ein',
    'complete_recaptcha_v3' => 'Fehler beim Generieren des reCAPTCHA-Tokens. Bitte überprüfen Sie, ob der Site-Schlüssel korrekt konfiguriert und gültig ist.',
    'failed_generate_v3_token' => 'Fehler beim Generieren des reCAPTCHA-Tokens. Bitte überprüfen Sie, ob der Site-Schlüssel korrekt konfiguriert und gültig ist.',
    'complete_recaptcha_v2' => 'Bitte vervollständigen Sie das reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Fehler beim Generieren des reCAPTCHA v2-Tokens.',
    'settings_saved' => 'Einstellungen gespeichert.',
    'failed_save_settings' => 'Fehler beim Speichern der Einstellungen. Bitte versuchen Sie es erneut.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Geheimschlüssel oder Antwort-Token ist ungültig',
    'captcha_verification_failed' => 'reCAPTCHA-Überprüfung fehlgeschlagen (Score/Aktion/Hostname-Nichtübereinstimmung)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'reCAPTCHA-Version ist erforderlich',
    'captcha_version_in' => 'Ausgewählte reCAPTCHA-Version ist ungültig',
    'failover_action_required' => 'Failover-Aktion ist erforderlich',
    'failover_action_in' => 'Ausgewählte Failover-Aktion ist ungültig',
    'score_threshold_numeric' => 'Score-Schwellenwert muss eine Zahl sein',
    'score_threshold_min' => 'Score-Schwellenwert muss mindestens 0 sein',
    'score_threshold_max' => 'Score-Schwellenwert darf nicht größer als 1 sein',
    'theme_required' => 'Thema ist erforderlich',
    'theme_in' => 'Ausgewähltes Thema ist ungültig',
    'size_required' => 'Größe ist erforderlich',
    'size_in' => 'Ausgewählte Größe ist ungültig',
    'badge_position_required' => 'Abzeichenposition ist erforderlich',
    'badge_position_in' => 'Ausgewählte Abzeichenposition ist ungültig',
];
