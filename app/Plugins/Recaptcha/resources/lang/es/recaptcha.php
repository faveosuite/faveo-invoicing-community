<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Configuración de reCAPTCHA',
    'captcha_configuration' => 'Configuración de reCAPTCHA',
    'captcha_version' => 'Versión de reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisible',
    'recaptcha_v2_checkbox' => 'Casilla de verificación de reCAPTCHA v2',
    'select_captcha_type' => 'Seleccione qué versión de reCAPTCHA usar',
    'failover_action' => 'Acción de conmutación por error',
    'none' => 'Ninguna',
    'fallback_v2_checkbox' => 'Volver a la casilla de verificación de reCAPTCHA v2',
    'action_if_captcha_fails' => 'Acción a tomar si reCAPTCHA falla',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Configuración de reCAPTCHA v3',
    'v3_site_key' => 'Clave del sitio v3',
    'enter_v3_site_key' => 'Ingrese la clave de su sitio reCAPTCHA v3',
    'v3_secret_key' => 'Clave secreta v3',
    'enter_v3_secret_key' => 'Ingrese su clave secreta de reCAPTCHA v3',
    'v3_score_threshold' => 'Umbral de puntuación v3',
    'v3_score_hint' => 'Valor entre 0.0 y 1.0 (más alto es mejor)',
    'v3_preview' => 'Vista previa de v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Configuración de reCAPTCHA v2',
    'v2_site_key' => 'Clave del sitio v2',
    'enter_v2_site_key' => 'Ingrese la clave de su sitio reCAPTCHA v2',
    'v2_secret_key' => 'Clave secreta v2',
    'enter_v2_secret_key' => 'Ingrese su clave secreta de reCAPTCHA v2',
    'v2_preview' => 'Vista previa de v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Apariencia',
    'theme' => 'Tema',
    'theme_light' => 'Claro',
    'theme_dark' => 'Oscuro',
    'size' => 'Tamaño',
    'size_normal' => 'Normal',
    'size_compact' => 'Compacto',
    'badge_position' => 'Posición de la insignia',
    'badge_bottomright' => 'Inferior derecha',
    'badge_bottomleft' => 'Inferior izquierda',
    'badge_inline' => 'En línea',

    /*
    * Common
    */
    'save' => 'Guardar',
    'saving' => 'Guardando',
    'home' => 'Inicio',
    'settings' => 'Configuración',

    /*
    * Success messages
    */
    'captcha_settings_updated' => '¡Configuración de reCAPTCHA actualizada correctamente!',

    /*
    * Error messages
    */
    'captcha_message' => 'Error en la verificación de reCAPTCHA. Vuelva a intentarlo.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Este campo es obligatorio cuando se cumple la condición',
    'select_captcha_version' => 'Seleccione una versión de reCAPTCHA',
    'v3_site_key_required' => 'Se requiere la clave del sitio reCAPTCHA v3',
    'v3_secret_key_required' => 'Se requiere la clave secreta de reCAPTCHA v3',
    'v2_site_key_required' => 'Se requiere la clave del sitio reCAPTCHA v2',
    'v2_secret_key_required' => 'Se requiere la clave secreta de reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'Ingrese una clave de sitio de reCAPTCHA válida',
    'valid_recaptcha_secret_key' => 'Ingrese una clave secreta de reCAPTCHA válida',
    'score_threshold_required' => 'Se requiere un umbral de puntuación para reCAPTCHA v3',
    'valid_number' => 'Ingrese un número válido',
    'complete_recaptcha_v3' => 'No se pudo generar el token de reCAPTCHA. Verifique que la clave del sitio esté configurada correctamente y sea válida.',
    'failed_generate_v3_token' => 'No se pudo generar el token de reCAPTCHA. Verifique que la clave del sitio esté configurada correctamente y sea válida.',
    'complete_recaptcha_v2' => 'Complete el reCAPTCHA v2.',
    'failed_generate_v2_token' => 'No se pudo generar el token de reCAPTCHA v2.',
    'settings_saved' => 'Configuración guardada.',
    'failed_save_settings' => 'No se pudo guardar la configuración. Vuelva a intentarlo.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'La clave secreta o el token de respuesta no son válidos',
    'captcha_verification_failed' => 'Error en la verificación de reCAPTCHA (no coincide la puntuación/acción/nombre de host)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'Se requiere la versión de reCAPTCHA',
    'captcha_version_in' => 'La versión de reCAPTCHA seleccionada no es válida',
    'failover_action_required' => 'Se requiere una acción de conmutación por error',
    'failover_action_in' => 'La acción de conmutación por error seleccionada no es válida',
    'score_threshold_numeric' => 'El umbral de puntuación debe ser un número',
    'score_threshold_min' => 'El umbral de puntuación debe ser de al menos 0',
    'score_threshold_max' => 'El umbral de puntuación no debe ser superior a 1',
    'theme_required' => 'Se requiere un tema',
    'theme_in' => 'El tema seleccionado no es válido',
    'size_required' => 'Se requiere un tamaño',
    'size_in' => 'El tamaño seleccionado no es válido',
    'badge_position_required' => 'Se requiere la posición de la insignia',
    'badge_position_in' => 'La posición de la insignia seleccionada no es válida',
];
