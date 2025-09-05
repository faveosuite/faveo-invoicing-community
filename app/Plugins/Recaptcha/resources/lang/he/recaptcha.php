<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'הגדרות Captcha',
    'captcha_configuration' => 'תצורת Captcha',
    'captcha_version' => 'גרסת Captcha',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 בלתי נראה',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 תיבת סימון',
    'select_captcha_type' => 'בחר באיזו גרסת reCAPTCHA להשתמש',
    'failover_action' => 'פעולת גיבוי',
    'none' => 'ללא',
    'fallback_v2_checkbox' => 'חזרה ל-reCAPTCHA v2 תיבת סימון',
    'action_if_captcha_fails' => 'פעולה לביצוע במקרה של כשל ב-reCAPTCHA',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'הגדרות reCAPTCHA v3',
    'v3_site_key' => 'מפתח אתר v3',
    'enter_v3_site_key' => 'הזן את מפתח האתר של reCAPTCHA v3',
    'v3_secret_key' => 'מפתח סודי v3',
    'enter_v3_secret_key' => 'הזן את המפתח הסודי של reCAPTCHA v3',
    'v3_score_threshold' => 'סף ציון v3',
    'v3_score_hint' => 'ערך בין 0.0 ל-1.0 (גבוה יותר עדיף)',
    'v3_preview' => 'תצוגה מקדימה v3',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'הגדרות reCAPTCHA v2',
    'v2_site_key' => 'מפתח אתר v2',
    'enter_v2_site_key' => 'הזן את מפתח האתר של reCAPTCHA v2',
    'v2_secret_key' => 'מפתח סודי v2',
    'enter_v2_secret_key' => 'הזן את המפתח הסודי של reCAPTCHA v2',
    'v2_preview' => 'תצוגה מקדימה v2',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'מראה והודעות',
    'theme' => 'ערכת עיצוב',
    'theme_light' => 'בהירה',
    'theme_dark' => 'כהה',
    'size' => 'גודל',
    'size_normal' => 'רגיל',
    'size_compact' => 'קומפקטי',
    'badge_position' => 'מיקום התג',
    'badge_bottomright' => 'למטה מימין',
    'badge_bottomleft' => 'למטה משמאל',
    'badge_inline' => 'בשורה',
    'error_message' => 'הודעת שגיאה',
    
    /*
    * Common
    */
    'save' => 'שמור',
    'saving' => 'שומר',
    'home' => 'דף הבית',
    'settings' => 'הגדרות',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'הגדרות reCAPTCHA עודכנו בהצלחה!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'אימות Captcha נכשל. אנא נסה שוב.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'שדה זה נדרש כאשר מתקיים התנאי',
    'select_captcha_version' => 'אנא בחר גרסת captcha',
    'v3_site_key_required' => 'מפתח אתר reCAPTCHA v3 נדרש',
    'v3_secret_key_required' => 'מפתח סודי reCAPTCHA v3 נדרש',
    'v2_site_key_required' => 'מפתח אתר reCAPTCHA v2 נדרש',
    'v2_secret_key_required' => 'מפתח סודי reCAPTCHA v2 נדרש',
    'valid_recaptcha_site_key' => 'אנא הזן מפתח אתר reCAPTCHA חוקי',
    'valid_recaptcha_secret_key' => 'אנא הזן מפתח סודי reCAPTCHA חוקי',
    'score_threshold_required' => 'סף הציון נדרש עבור reCAPTCHA v3',
    'valid_number' => 'אנא הזן מספר חוקי',
    'complete_recaptcha_v3' => 'אנא השלם את reCAPTCHA v3.',
    'failed_generate_v3_token' => 'נכשל ביצירת טוקן reCAPTCHA v3.',
    'complete_recaptcha_v2' => 'אנא השלם את reCAPTCHA v2.',
    'failed_generate_v2_token' => 'נכשל ביצירת טוקן reCAPTCHA v2.',
    'settings_saved' => 'ההגדרות נשמרו.',
    'failed_save_settings' => 'נכשל בשמירת ההגדרות. אנא נסה שוב.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'מפתח סודי או טוקן תגובה אינם חוקיים',
    'captcha_verification_failed' => 'אימות Captcha נכשל (אי התאמה בציון/פעולה/שם מארח)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'גרסת Captcha נדרשת',
    'captcha_version_in' => 'גרסת Captcha שנבחרה אינה חוקית',
    'failover_action_required' => 'פעולת גיבוי נדרשת',
    'failover_action_in' => 'פעולת הגיבוי שנבחרה אינה חוקית',
    'score_threshold_numeric' => 'סף הציון חייב להיות מספר',
    'score_threshold_min' => 'סף הציון חייב להיות לפחות 0',
    'score_threshold_max' => 'סף הציון לא יכול להיות גדול מ-1',
    'error_message_required' => 'הודעת שגיאה נדרשת',
    'error_message_max' => 'הודעת השגיאה לא יכולה לעלות על 255 תווים',
    'theme_required' => 'ערכת העיצוב נדרשת',
    'theme_in' => 'ערכת העיצוב שנבחרה אינה חוקית',
    'size_required' => 'גודל נדרש',
    'size_in' => 'הגודל שנבחר אינו חוקי',
    'badge_position_required' => 'מיקום התג נדרש',
    'badge_position_in' => 'מיקום התג שנבחר אינו חוקי',
];