<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Настройки reCAPTCHA',
    'captcha_configuration' => 'Конфигурация reCAPTCHA',
    'captcha_version' => 'Версия reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Невидимая',
    'recaptcha_v2_checkbox' => 'Флажок reCAPTCHA v2',
    'select_captcha_type' => 'Выберите, какую версию reCAPTCHA использовать',
    'failover_action' => 'Действие при сбое',
    'none' => 'Нет',
    'fallback_v2_checkbox' => 'Вернуться к флажку reCAPTCHA v2',
    'action_if_captcha_fails' => 'Действие, которое необходимо предпринять в случае сбоя reCAPTCHA',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Настройки reCAPTCHA v3',
    'v3_site_key' => 'Ключ сайта v3',
    'enter_v3_site_key' => 'Введите ключ вашего сайта reCAPTCHA v3',
    'v3_secret_key' => 'Секретный ключ v3',
    'enter_v3_secret_key' => 'Введите ваш секретный ключ reCAPTCHA v3',
    'v3_score_threshold' => 'Порог оценки v3',
    'v3_score_hint' => 'Значение от 0,0 до 1,0 (чем выше, тем лучше)',
    'v3_preview' => 'Предварительный просмотр v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Настройки reCAPTCHA v2',
    'v2_site_key' => 'Ключ сайта v2',
    'enter_v2_site_key' => 'Введите ключ вашего сайта reCAPTCHA v2',
    'v2_secret_key' => 'Секретный ключ v2',
    'enter_v2_secret_key' => 'Введите ваш секретный ключ reCAPTCHA v2',
    'v2_preview' => 'Предварительный просмотр v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Внешний вид',
    'theme' => 'Тема',
    'theme_light' => 'Светлая',
    'theme_dark' => 'Темная',
    'size' => 'Размер',
    'size_normal' => 'Обычный',
    'size_compact' => 'Компактный',
    'badge_position' => 'Положение значка',
    'badge_bottomright' => 'Внизу справа',
    'badge_bottomleft' => 'Внизу слева',
    'badge_inline' => 'Встроенный',

    /*
    * Common
    */
    'save' => 'Сохранить',
    'saving' => 'Сохранение',
    'home' => 'Главная',
    'settings' => 'Настройки',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Настройки reCAPTCHA успешно обновлены!',

    /*
    * Error messages
    */
    'captcha_message' => 'Проверка reCAPTCHA не удалась. Пожалуйста, попробуйте еще раз.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Это поле обязательно для заполнения при выполнении условия',
    'select_captcha_version' => 'Выберите версию reCAPTCHA',
    'v3_site_key_required' => 'Требуется ключ сайта reCAPTCHA v3',
    'v3_secret_key_required' => 'Требуется секретный ключ reCAPTCHA v3',
    'v2_site_key_required' => 'Требуется ключ сайта reCAPTCHA v2',
    'v2_secret_key_required' => 'Требуется секретный ключ reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'Введите действительный ключ сайта reCAPTCHA',
    'valid_recaptcha_secret_key' => 'Введите действительный секретный ключ reCAPTCHA',
    'score_threshold_required' => 'Требуется порог оценки для reCAPTCHA v3',
    'valid_number' => 'Введите действительное число',
    'complete_recaptcha_v3' => 'Не удалось сгенерировать токен reCAPTCHA. Убедитесь, что ключ сайта настроен правильно и действителен.',
    'failed_generate_v3_token' => 'Не удалось сгенерировать токен reCAPTCHA. Убедитесь, что ключ сайта настроен правильно и действителен.',
    'complete_recaptcha_v2' => 'Завершите reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Не удалось сгенерировать токен reCAPTCHA v2.',
    'settings_saved' => 'Настройки сохранены.',
    'failed_save_settings' => 'Не удалось сохранить настройки. Пожалуйста, попробуйте еще раз.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Неверный секретный ключ или токен ответа',
    'captcha_verification_failed' => 'Проверка reCAPTCHA не удалась (несоответствие оценки/действия/имени хоста)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'Требуется версия reCAPTCHA',
    'captcha_version_in' => 'Выбранная версия reCAPTCHA недействительна',
    'failover_action_required' => 'Требуется действие при сбое',
    'failover_action_in' => 'Выбранное действие при сбое недействительно',
    'score_threshold_numeric' => 'Порог оценки должен быть числом',
    'score_threshold_min' => 'Порог оценки должен быть не менее 0',
    'score_threshold_max' => 'Порог оценки не должен превышать 1',
    'theme_required' => 'Требуется тема',
    'theme_in' => 'Выбранная тема недействительна',
    'size_required' => 'Требуется размер',
    'size_in' => 'Выбранный размер недействителен',
    'badge_position_required' => 'Требуется положение значка',
    'badge_position_in' => 'Выбранное положение значка недействительно',
];
