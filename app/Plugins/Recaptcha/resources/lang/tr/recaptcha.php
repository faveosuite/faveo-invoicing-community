<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Captcha Ayarları',
    'captcha_configuration' => 'Captcha Yapılandırması',
    'captcha_version' => 'Captcha Sürümü',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Görünmez',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Onay Kutusu',
    'select_captcha_type' => 'Hangi reCAPTCHA sürümünü kullanacağınızı seçin',
    'failover_action' => 'Yük devretme eylemi',
    'none' => 'Yok',
    'fallback_v2_checkbox' => 'reCAPTCHA v2 Onay Kutusuna geri dön',
    'action_if_captcha_fails' => 'reCAPTCHA başarısız olursa yapılacak eylem',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 Ayarları',
    'v3_site_key' => 'v3 Site Anahtarı',
    'enter_v3_site_key' => 'reCAPTCHA v3 site anahtarınızı girin',
    'v3_secret_key' => 'v3 Gizli Anahtar',
    'enter_v3_secret_key' => 'reCAPTCHA v3 gizli anahtarınızı girin',
    'v3_score_threshold' => 'v3 Puan Eşiği',
    'v3_score_hint' => '0,0 ile 1,0 arasında değer (yüksek olması daha iyidir)',
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
    * Appearance & Messages
    */
    'appearance_messages' => 'Görünüm ve Mesajlar',
    'theme' => 'Tema',
    'theme_light' => 'Açık',
    'theme_dark' => 'Koyu',
    'size' => 'Boyut',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompakt',
    'badge_position' => 'Rozet konumu',
    'badge_bottomright' => 'Sağ Alt',
    'badge_bottomleft' => 'Sol Alt',
    'badge_inline' => 'Satır içi',
    
    /*
    * Common
    */
    'save' => 'Kaydet',
    'saving' => 'Kaydediliyor',
    'home' => 'Ana Sayfa',
    'settings' => 'Ayarlar',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA ayarları başarıyla güncellendi!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'Captcha doğrulaması başarısız oldu. Lütfen tekrar deneyin.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Koşul karşılandığında bu alan zorunludur',
    'select_captcha_version' => 'Lütfen bir captcha sürümü seçin',
    'v3_site_key_required' => 'reCAPTCHA v3 site anahtarı gerekli',
    'v3_secret_key_required' => 'reCAPTCHA v3 gizli anahtarı gerekli',
    'v2_site_key_required' => 'reCAPTCHA v2 site anahtarı gerekli',
    'v2_secret_key_required' => 'reCAPTCHA v2 gizli anahtarı gerekli',
    'valid_recaptcha_site_key' => 'Lütfen geçerli bir reCAPTCHA site anahtarı girin',
    'valid_recaptcha_secret_key' => 'Lütfen geçerli bir reCAPTCHA gizli anahtarı girin',
    'score_threshold_required' => 'reCAPTCHA v3 için puan eşiği gerekli',
    'valid_number' => 'Lütfen geçerli bir sayı girin',
    'complete_recaptcha_v3' => 'Lütfen reCAPTCHA v3\'ü tamamlayın.',
    'failed_generate_v3_token' => 'reCAPTCHA v3 belirteci oluşturulamadı.',
    'complete_recaptcha_v2' => 'Lütfen reCAPTCHA v2\'yi tamamlayın.',
    'failed_generate_v2_token' => 'reCAPTCHA v2 belirteci oluşturulamadı.',
    'settings_saved' => 'Ayarlar kaydedildi.',
    'failed_save_settings' => 'Ayarlar kaydedilemedi. Lütfen tekrar deneyin.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Gizli anahtar veya yanıt belirteci geçersiz',
    'captcha_verification_failed' => 'Captcha doğrulaması başarısız oldu (puan/eylem/ana bilgisayar adı uyuşmazlığı)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'Captcha sürümü gerekli',
    'captcha_version_in' => 'Seçilen captcha sürümü geçersiz',
    'failover_action_required' => 'Yük devretme eylemi gerekli',
    'failover_action_in' => 'Seçilen yük devretme eylemi geçersiz',
    'score_threshold_numeric' => 'Puan eşiği bir sayı olmalıdır',
    'score_threshold_min' => 'Puan eşiği en az 0 olmalıdır',
    'score_threshold_max' => 'Puan eşiği 1\'den büyük olmamalıdır',
    'theme_required' => 'Tema gerekli',
    'theme_in' => 'Seçilen tema geçersiz',
    'size_required' => 'Boyut gerekli',
    'size_in' => 'Seçilen boyut geçersiz',
    'badge_position_required' => 'Rozet konumu gerekli',
    'badge_position_in' => 'Seçilen rozet konumu geçersiz',
];