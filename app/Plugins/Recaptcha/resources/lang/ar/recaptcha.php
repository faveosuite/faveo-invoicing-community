<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'إعدادات Captcha',
    'captcha_configuration' => 'تكوين Captcha',
    'captcha_version' => 'إصدار Captcha',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 غير مرئي',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 مربع اختيار',
    'select_captcha_type' => 'حدد إصدار reCAPTCHA الذي تريد استخدامه',
    'failover_action' => 'إجراء التجاوز',
    'none' => 'لا شيء',
    'fallback_v2_checkbox' => 'الرجوع إلى reCAPTCHA v2 مربع اختيار',
    'action_if_captcha_fails' => 'الإجراء المطلوب عند فشل reCAPTCHA',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'إعدادات reCAPTCHA v3',
    'v3_site_key' => 'مفتاح الموقع v3',
    'enter_v3_site_key' => 'أدخل مفتاح موقع reCAPTCHA v3',
    'v3_secret_key' => 'المفتاح السري v3',
    'enter_v3_secret_key' => 'أدخل المفتاح السري ل reCAPTCHA v3',
    'v3_score_threshold' => 'عتبة النقاط v3',
    'v3_score_hint' => 'القيمة بين 0.0 و 1.0 (كلما كانت أعلى كانت أفضل)',
    'v3_preview' => 'معاينة v3',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'إعدادات reCAPTCHA v2',
    'v2_site_key' => 'مفتاح الموقع v2',
    'enter_v2_site_key' => 'أدخل مفتاح موقع reCAPTCHA v2',
    'v2_secret_key' => 'المفتاح السري v2',
    'enter_v2_secret_key' => 'أدخل المفتاح السري ل reCAPTCHA v2',
    'v2_preview' => 'معاينة v2',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'المظهر والرسائل',
    'theme' => 'السمة',
    'theme_light' => 'فاتح',
    'theme_dark' => 'داكن',
    'size' => 'الحجم',
    'size_normal' => 'عادي',
    'size_compact' => 'مدمج',
    'badge_position' => 'موضع الشارة',
    'badge_bottomright' => 'أسفل اليمين',
    'badge_bottomleft' => 'أسفل اليسار',
    'badge_inline' => 'في السطر',
    
    /*
    * Common
    */
    'save' => 'حفظ',
    'saving' => 'جاري الحفظ',
    'home' => 'الرئيسية',
    'settings' => 'الإعدادات',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'تم تحديث إعدادات reCAPTCHA بنجاح!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'فشل التحقق من Captcha. يرجى المحاولة مرة أخرى.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'هذا الحقل مطلوب عندما يتم استيفاء الشرط',
    'select_captcha_version' => 'يرجى تحديد إصدار captcha',
    'v3_site_key_required' => 'مفتاح موقع reCAPTCHA v3 مطلوب',
    'v3_secret_key_required' => 'المفتاح السري ل reCAPTCHA v3 مطلوب',
    'v2_site_key_required' => 'مفتاح موقع reCAPTCHA v2 مطلوب',
    'v2_secret_key_required' => 'المفتاح السري ل reCAPTCHA v2 مطلوب',
    'valid_recaptcha_site_key' => 'يرجى إدخال مفتاح موقع reCAPTCHA صالح',
    'valid_recaptcha_secret_key' => 'يرجى إدخال مفتاح سري reCAPTCHA صالح',
    'score_threshold_required' => 'عتبة النقاط مطلوبة ل reCAPTCHA v3',
    'valid_number' => 'يرجى إدخال رقم صالح',
    'complete_recaptcha_v3' => 'يرجى إكمال reCAPTCHA v3.',
    'failed_generate_v3_token' => 'فشل في إنشاء رمز reCAPTCHA v3.',
    'complete_recaptcha_v2' => 'يرجى إكمال reCAPTCHA v2.',
    'failed_generate_v2_token' => 'فشل في إنشاء رمز reCAPTCHA v2.',
    'settings_saved' => 'تم حفظ الإعدادات.',
    'failed_save_settings' => 'فشل في حفظ الإعدادات. يرجى المحاولة مرة أخرى.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'المفتاح السري أو رمز الاستجابة غير صالح',
    'captcha_verification_failed' => 'فشل التحقق من Captcha (عدم تطابق النقاط/الإجراء/اسم المضيف)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'إصدار Captcha مطلوب',
    'captcha_version_in' => 'إصدار Captcha المحدد غير صالح',
    'failover_action_required' => 'إجراء التجاوز مطلوب',
    'failover_action_in' => 'إجراء التجاوز المحدد غير صالح',
    'score_threshold_numeric' => 'عتبة النقاط يجب أن تكون رقماً',
    'score_threshold_min' => 'عتبة النقاط يجب أن تكون على الأقل 0',
    'score_threshold_max' => 'عتبة النقاط يجب ألا تتجاوز 1',
    'theme_required' => 'السمة مطلوبة',
    'theme_in' => 'السمة المحددة غير صالحة',
    'size_required' => 'الحجم مطلوب',
    'size_in' => 'الحجم المحدد غير صالح',
    'badge_position_required' => 'موضع الشارة مطلوب',
    'badge_position_in' => 'موضع الشارة المحدد غير صالح',
];