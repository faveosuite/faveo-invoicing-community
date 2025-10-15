<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'إعدادات reCAPTCHA',
    'captcha_configuration' => 'تكوين reCAPTCHA',
    'captcha_version' => 'إصدار reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 غير مرئي',
    'recaptcha_v2_checkbox' => 'خانة اختيار reCAPTCHA v2',
    'select_captcha_type' => 'حدد إصدار reCAPTCHA الذي تريد استخدامه',
    'failover_action' => 'إجراء تجاوز الفشل',
    'none' => 'لا شيء',
    'fallback_v2_checkbox' => 'الرجوع إلى خانة اختيار reCAPTCHA v2',
    'action_if_captcha_fails' => 'الإجراء الذي يجب اتخاذه في حالة فشل reCAPTCHA',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'إعدادات reCAPTCHA v3',
    'v3_site_key' => 'مفتاح موقع v3',
    'enter_v3_site_key' => 'أدخل مفتاح موقع reCAPTCHA v3 الخاص بك',
    'v3_secret_key' => 'المفتاح السري v3',
    'enter_v3_secret_key' => 'أدخل المفتاح السري reCAPTCHA v3 الخاص بك',
    'v3_score_threshold' => 'عتبة نقاط v3',
    'v3_score_hint' => 'قيمة بين 0.0 و 1.0 (الأعلى هو الأفضل)',
    'v3_preview' => 'معاينة v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'إعدادات reCAPTCHA v2',
    'v2_site_key' => 'مفتاح موقع v2',
    'enter_v2_site_key' => 'أدخل مفتاح موقع reCAPTCHA v2 الخاص بك',
    'v2_secret_key' => 'المفتاح السري v2',
    'enter_v2_secret_key' => 'أدخل المفتاح السري reCAPTCHA v2 الخاص بك',
    'v2_preview' => 'معاينة v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'المظهر',
    'theme' => 'المظهر',
    'theme_light' => 'فاتح',
    'theme_dark' => 'داكن',
    'size' => 'الحجم',
    'size_normal' => 'عادي',
    'size_compact' => 'مضغوط',
    'badge_position' => 'موضع الشارة',
    'badge_bottomright' => 'أسفل اليمين',
    'badge_bottomleft' => 'أسفل اليسار',
    'badge_inline' => 'مضمن',

    /*
    * Common
    */
    'save' => 'حفظ',
    'saving' => 'جارٍ الحفظ',
    'home' => 'الرئيسية',
    'settings' => 'الإعدادات',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'تم تحديث إعدادات reCAPTCHA بنجاح!',

    /*
    * Error messages
    */
    'captcha_message' => 'فشل التحقق من reCAPTCHA. يرجى المحاولة مرة أخرى.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'هذا الحقل مطلوب عند استيفاء الشرط',
    'select_captcha_version' => 'الرجاء تحديد إصدار reCAPTCHA',
    'v3_site_key_required' => 'مفتاح موقع reCAPTCHA v3 مطلوب',
    'v3_secret_key_required' => 'المفتاح السري reCAPTCHA v3 مطلوب',
    'v2_site_key_required' => 'مفتاح موقع reCAPTCHA v2 مطلوب',
    'v2_secret_key_required' => 'المفتاح السري reCAPTCHA v2 مطلوب',
    'valid_recaptcha_site_key' => 'الرجاء إدخال مفتاح موقع reCAPTCHA صالح',
    'valid_recaptcha_secret_key' => 'الرجاء إدخال مفتاح سري reCAPTCHA صالح',
    'score_threshold_required' => 'عتبة النقاط مطلوبة لـ reCAPTCHA v3',
    'valid_number' => 'الرجاء إدخال رقم صالح',
    'complete_recaptcha_v3' => 'فشل في إنشاء رمز reCAPTCHA. يرجى التحقق من أن مفتاح الموقع تم تكوينه بشكل صحيح وصالح.',
    'failed_generate_v3_token' => 'فشل في إنشاء رمز reCAPTCHA. يرجى التحقق من أن مفتاح الموقع تم تكوينه بشكل صحيح وصالح.',
    'complete_recaptcha_v2' => 'الرجاء إكمال reCAPTCHA v2.',
    'failed_generate_v2_token' => 'فشل في إنشاء رمز reCAPTCHA v2.',
    'settings_saved' => 'تم حفظ الإعدادات.',
    'failed_save_settings' => 'فشل في حفظ الإعدادات. يرجى المحاولة مرة أخرى.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'المفتاح السري أو رمز الاستجابة غير صالح',
    'captcha_verification_failed' => 'فشل التحقق من reCAPTCHA (عدم تطابق النقاط/الإجراء/اسم المضيف)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'إصدار reCAPTCHA مطلوب',
    'captcha_version_in' => 'إصدار reCAPTCHA المحدد غير صالح',
    'failover_action_required' => 'إجراء تجاوز الفشل مطلوب',
    'failover_action_in' => 'إجراء تجاوز الفشل المحدد غير صالح',
    'score_threshold_numeric' => 'يجب أن تكون عتبة النقاط رقمًا',
    'score_threshold_min' => 'يجب أن تكون عتبة النقاط 0 على الأقل',
    'score_threshold_max' => 'يجب ألا تزيد عتبة النقاط عن 1',
    'theme_required' => 'المظهر مطلوب',
    'theme_in' => 'المظهر المحدد غير صالح',
    'size_required' => 'الحجم مطلوب',
    'size_in' => 'الحجم المحدد غير صالح',
    'badge_position_required' => 'موضع الشارة مطلوب',
    'badge_position_in' => 'موضع الشارة المحدد غير صالح',
];
