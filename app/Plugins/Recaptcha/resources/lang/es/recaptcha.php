<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Configuración de Captcha',
    'captcha_configuration' => 'Configuración de Captcha',
    'captcha_version' => 'Versión de Captcha',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisible',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Casilla de verificación',
    'select_captcha_type' => 'Seleccione qué versión de reCAPTCHA usar',
    'failover_action' => 'Acción de conmutación por error',
    'none' => 'Ninguno',
    'fallback_v2_checkbox' => 'Volver a reCAPTCHA v2 Casilla de verificación',
    'action_if_captcha_fails' => 'Acción a tomar si reCAPTCHA falla',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Configuración reCAPTCHA v3',
    'v3_site_key' => 'Clave del sitio v3',
    'enter_v3_site_key' => 'Ingrese su clave de sitio reCAPTCHA v3',
    'v3_secret_key' => 'Clave secreta v3',
    'enter_v3_secret_key' => 'Ingrese su clave secreta reCAPTCHA v3',
    'v3_score_threshold' => 'Umbral de puntuación v3',
    'v3_score_hint' => 'Valor entre 0.0 y 1.0 (mayor es mejor)',
    'v3_preview' => 'Vista previa v3',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Configuración reCAPTCHA v2',
    'v2_site_key' => 'Clave del sitio v2',
    'enter_v2_site_key' => 'Ingrese su clave de sitio reCAPTCHA v2',
    'v2_secret_key' => 'Clave secreta v2',
    'enter_v2_secret_key' => 'Ingrese su clave secreta reCAPTCHA v2',
    'v2_preview' => 'Vista previa v2',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Apariencia y mensajes',
    'theme' => 'Tema',
    'theme_light' => 'Claro',
    'theme_dark' => 'Oscuro',
    'size' => 'Tamaño',
    'size_normal' => 'Normal',
    'size_compact' => 'Compacto',
    'badge_position' => 'Posición de la insignia',
    'badge_bottomright' => 'Abajo a la derecha',
    'badge_bottomleft' => 'Abajo a la izquierda',
    'badge_inline' => 'En línea',
    'error_message' => 'Mensaje de error',
    
    /*
    * Common
    */
    'save' => 'Guardar',
    'saving' => 'Guardando',
    'home' => 'Inicio',
    'settings' => 'Configuraciones',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => '¡Configuración de reCAPTCHA actualizada con éxito!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'La verificación de captcha falló. Por favor, inténtelo de nuevo.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Este campo es obligatorio cuando se cumple la condición',
    'select_captcha_version' => 'Por favor seleccione una versión de captcha',
    'v3_site_key_required' => 'Se requiere la clave del sitio reCAPTCHA v3',
    'v3_secret_key_required' => 'Se requiere la clave secreta reCAPTCHA v3',
    'v2_site_key_required' => 'Se requiere la clave del sitio reCAPTCHA v2',
    'v2_secret_key_required' => 'Se requiere la clave secreta reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'Por favor ingrese una clave de sitio reCAPTCHA válida',
    'valid_recaptcha_secret_key' => 'Por favor ingrese una clave secreta reCAPTCHA válida',
    'score_threshold_required' => 'Se requiere umbral de puntuación para reCAPTCHA v3',
    'valid_number' => 'Por favor ingrese un número válido',
    'complete_recaptcha_v3' => 'Por favor complete el reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Error al generar el token reCAPTCHA v3.',
    'complete_recaptcha_v2' => 'Por favor complete el reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Error al generar el token reCAPTCHA v2.',
    'settings_saved' => 'Configuración guardada.',
    'failed_save_settings' => 'Error al guardar la configuración. Por favor inténtelo de nuevo.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Clave secreta o token de respuesta no válido',
    'captcha_verification_failed' => 'La verificación de Captcha falló (desajuste de puntuación/acción/nombre de host)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'La versión de Captcha es requerida',
    'captcha_version_in' => 'La versión de Captcha seleccionada es inválida',
    'failover_action_required' => 'La acción de conmutación por error es requerida',
    'failover_action_in' => 'La acción de conmutación por error seleccionada es inválida',
    'score_threshold_numeric' => 'El umbral de puntuación debe ser un número',
    'score_threshold_min' => 'El umbral de puntuación debe ser al menos 0',
    'score_threshold_max' => 'El umbral de puntuación no debe ser mayor que 1',
    'error_message_required' => 'El mensaje de error es requerido',
    'error_message_max' => 'El mensaje de error no debe exceder 255 caracteres',
    'theme_required' => 'El tema es requerido',
    'theme_in' => 'El tema seleccionado es inválido',
    'size_required' => 'El tamaño es requerido',
    'size_in' => 'El tamaño seleccionado es inválido',
    'badge_position_required' => 'La posición de la insignia es requerida',
    'badge_position_in' => 'La posición de la insignia seleccionada es inválida',
];