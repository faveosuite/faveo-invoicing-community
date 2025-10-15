<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'הגדרות reCAPTCHA',
    'captcha_configuration' => 'תצורת reCAPTCHA',
    'captcha_version' => 'גרסת reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 בלתי נראה',
    'recaptcha_v2_checkbox' => 'תיבת סימון reCAPTCHA v2',
    'select_captcha_type' => 'בחר באיזו גרסה של reCAPTCHA להשתמש',
    'failover_action' => 'פעולת גיבוי',
    'none' => 'ללא',
    'fallback_v2_checkbox' => 'חזור לתיבת הסימון של reCAPTCHA v2',
    'action_if_captcha_fails' => 'פעולה שיש לנקוט אם reCAPTCHA נכשל',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'הגדרות reCAPTCHA v3',
    'v3_site_key' => 'מפתח אתר v3',
    'enter_v3_site_key' => 'הזן את מפתח האתר שלך ל-reCAPTCHA v3',
    'v3_secret_key' => 'מפתח סודי v3',
    'enter_v3_secret_key' => 'הזן את המפתח הסודי שלך ל-reCAPTCHA v3',
    'v3_score_threshold' => 'סף ניקוד v3',
    'v3_score_hint' => 'ערך בין 0.0 ל-1.0 (גבוה יותר טוב יותר)',
    'v3_preview' => 'תצוגה מקדימה של v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'הגדרות reCAPTCHA v2',
    'v2_site_key' => 'מפתח אתר v2',
    'enter_v2_site_key' => 'הזן את מפתח האתר שלך ל-reCAPTCHA v2',
    'v2_secret_key' => 'מפתח סודי v2',
    'enter_v2_secret_key' => 'הזן את המפתח הסודי שלך ל-reCAPTCHA v2',
    'v2_preview' => 'תצוגה מקדימה של v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'מראה',
    'theme' => 'ערכת נושא',
    'theme_light' => 'בהיר',
    'theme_dark' => 'כהה',
    'size' => 'גודל',
    'size_normal' => 'רגיל',
    'size_compact' => 'קומפקטי',
    'badge_position' => 'מיקום התג',
    'badge_bottomright' => 'למטה מימין',
    'badge_bottomleft' => 'למטה משמאל',
    'badge_inline' => 'בשורה',

    /*
    * Common
    */
    'save' => 'שמור',
    'saving' => 'שומר',
    'home' => 'בית',
    'settings' => 'הגדרות',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'הגדרות reCAPTCHA עודכנו בהצלחה!',

    /*
    * Error messages
    */
    'captcha_message' => 'אימות reCAPTCHA נכשל. אנא נסה שוב.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'שדה זה נדרש כאשר התנאי מתקיים',
    'select_captcha_version' => 'אנא בחר גרסת reCAPTCHA',
    'v3_site_key_required' => 'נדרש מפתח אתר reCAPTCHA v3',
    'v3_secret_key_required' => 'נדרש מפתח סודי reCAPTCHA v3',
    'v2_site_key_required' => 'נדרש מפתח אתר reCAPTCHA v2',
    'v2_secret_key_required' => 'נדרש מפתח סודי reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'אנא הזן מפתח אתר reCAPTCHA חוקי',
    'valid_recaptcha_secret_key' => 'אנא הזן מפתח סודי reCAPTCHA חוקי',
    'score_threshold_required' => 'סף הניקוד נדרש עבור reCAPTCHA v3',
    'valid_number' => 'אנא הזן מספר חוקי',
    'complete_recaptcha_v3' => 'יצירת אסימון reCAPTCHA נכשלה. אנא ודא שמפתח האתר מוגדר כהלכה ותקף.',
    'failed_generate_v3_token' => 'יצירת אסימון reCAPTCHA נכשלה. אנא ודא שמפתח האתר מוגדר כהלכה ותקף.',
    'complete_recaptcha_v2' => 'אנא השלם את reCAPTCHA v2.',
    'failed_generate_v2_token' => 'יצירת אסימון reCAPTCHA v2 נכשלה.',
    'settings_saved' => 'ההגדרות נשמרו.',
    'failed_save_settings' => 'שמירת ההגדרות נכשלה. אנא נסה שוב.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'מפתח סודי או אסימון תגובה אינו חוקי',
    'captcha_verification_failed' => 'אימות reCAPTCHA נכשל (אי התאמה בניקוד/פעולה/שם מארח)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'נדרשת גרסת reCAPTCHA',
    'captcha_version_in' => 'גרסת reCAPTCHA שנבחרה אינה חוקית',
    'failover_action_required' => 'נדרשת פעולת גיבוי',
    'failover_action_in' => 'פעולת הגיבוי שנבחרה אינה חוקית',
    'score_threshold_numeric' => 'סף הניקוד חייב להיות מספר',
    'score_threshold_min' => 'סף הניקוד חייב להיות לפחות 0',
    'score_threshold_max' => 'סף הניקוד לא יכול להיות גדול מ-1',
    'theme_required' => 'נדרשת ערכת נושא',
    'theme_in' => 'ערכת הנושא שנבחרה אינה חוקית',
    'size_required' => 'נדרש גודל',
    'size_in' => 'הגודל שנבחר אינו חוקי',
    'badge_position_required' => 'נדרש מיקום תג',
    'badge_position_in' => 'מיקום התג שנבחר אינו חוקי',
];
