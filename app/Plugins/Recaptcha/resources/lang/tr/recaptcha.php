<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'reCAPTCHA Ayarları',
    'captcha_configuration' => 'reCAPTCHA Yapılandırması',
    'captcha_version' => 'reCAPTCHA Sürümü',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Görünmez',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Onay Kutusu',
    'select_captcha_type' => 'Hangi reCAPTCHA sürümünün kullanılacağını seçin',
    'failover_action' => 'Yük Devretme Eylemi',
    'none' => 'Yok',
    'fallback_v2_checkbox' => 'reCAPTCHA v2 Onay Kutusuna Geri Dön',
    'action_if_captcha_fails' => 'reCAPTCHA başarısız olursa gerçekleştirilecek eylem',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 Ayarları',
    'v3_site_key' => 'v3 Site Anahtarı',
    'enter_v3_site_key' => 'reCAPTCHA v3 site anahtarınızı girin',
    'v3_secret_key' => 'v3 Gizli Anahtar',
    'enter_v3_secret_key' => 'reCAPTCHA v3 gizli anahtarınızı girin',
    'v3_score_threshold' => 'v3 Puan Eşiği',
    'v3_score_hint' => '0.0 ile 1.0 arasında bir değer (daha yüksek daha iyidir)',
    'v3_preview' => 'v3 Önizleme',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 Ayarları',
    'v2_site_key' => 'v2 Site Anahtarı',
    'enter_v2_site_key' => 'reCAPTCHA v2 site anahtarınızı girin',
    'v2_secret_key' => 'v2 Gizli Anahtar',
    'enter_v2_secret_key' => 'reCAPTCHA v2 gizli anahtarınızı girin',
    'v2_preview' => 'v2 Önizleme',

    /*
    * Appearance
    */
    'appearance_messages' => 'Görünüm',
    'theme' => 'Tema',
    'theme_light' => 'Açık',
    'theme_dark' => 'Koyu',
    'size' => 'Boyut',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Rozet Konumu',
    'badge_bottomright' => 'Sağ Alt',
    'badge_bottomleft' => 'Sol Alt',
    'badge_inline' => 'Satır İçi',

    /*
    * Common
    */
    'save' => 'Kaydet',
    'saving' => 'Kaydediliyor',
    'home' => 'Anasayfa',
    'settings' => 'Ayarlar',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA ayarları başarıyla güncellendi!',

    /*
    * Error messages
    */
    'captcha_message' => 'reCAPTCHA doğrulaması başarısız oldu. Lütfen tekrar deneyin.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Koşul karşılandığında bu alan zorunludur',
    'select_captcha_version' => 'Lütfen bir reCAPTCHA sürümü seçin',
    'v3_site_key_required' => 'reCAPTCHA v3 site anahtarı gereklidir',
    'v3_secret_key_required' => 'reCAPTCHA v3 gizli anahtarı gereklidir',
    'v2_site_key_required' => 'reCAPTCHA v2 site anahtarı gereklidir',
    'v2_secret_key_required' => 'reCAPTCHA v2 gizli anahtarı gereklidir',
    'valid_recaptcha_site_key' => 'Lütfen geçerli bir reCAPTCHA site anahtarı girin',
    'valid_recaptcha_secret_key' => 'Lütfen geçerli bir reCAPTCHA gizli anahtarı girin',
    'score_threshold_required' => 'reCAPTCHA v3 için puan eşiği gereklidir',
    'valid_number' => 'Lütfen geçerli bir numara girin',
    'complete_recaptcha_v3' => 'reCAPTCHA jetonu oluşturulamadı. Lütfen site anahtarının doğru yapılandırıldığını ve geçerli olduğunu doğrulayın.',
    'failed_generate_v3_token' => 'reCAPTCHA jetonu oluşturulamadı. Lütfen site anahtarının doğru yapılandırıldığını ve geçerli olduğunu doğrulayın.',
    'complete_recaptcha_v2' => 'Lütfen reCAPTCHA v2\'yi tamamlayın.',
    'failed_generate_v2_token' => 'reCAPTCHA v2 jetonu oluşturulamadı.',
    'settings_saved' => 'Ayarlar kaydedildi.',
    'failed_save_settings' => 'Ayarlar kaydedilemedi. Lütfen tekrar deneyin.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Gizli anahtar veya yanıt jetonu geçersiz',
    'captcha_verification_failed' => 'reCAPTCHA doğrulaması başarısız oldu (puan/eylem/ana bilgisayar adı uyuşmazlığı)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'reCAPTCHA sürümü gereklidir',
    'captcha_version_in' => 'Seçilen reCAPTCHA sürümü geçersiz',
    'failover_action_required' => 'Yük devretme eylemi gereklidir',
    'failover_action_in' => 'Seçilen yük devretme eylemi geçersiz',
    'score_threshold_numeric' => 'Puan eşiği bir sayı olmalıdır',
    'score_threshold_min' => 'Puan eşiği en az 0 olmalıdır',
    'score_threshold_max' => 'Puan eşiği 1\'den büyük olmamalıdır',
    'theme_required' => 'Tema gereklidir',
    'theme_in' => 'Seçilen tema geçersiz',
    'size_required' => 'Boyut gereklidir',
    'size_in' => 'Seçilen boyut geçersiz',
    'badge_position_required' => 'Rozet konumu gereklidir',
    'badge_position_in' => 'Seçilen rozet konumu geçersiz',
];
