<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => '驗證碼設定',
    'captcha_configuration' => '驗證碼設定',
    'captcha_version' => '驗證碼版本',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 隱形',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 核取方塊',
    'select_captcha_type' => '選擇要使用的 reCAPTCHA 版本',
    'failover_action' => '故障轉移操作',
    'none' => '無',
    'fallback_v2_checkbox' => '退回至 reCAPTCHA v2 核取方塊',
    'action_if_captcha_fails' => 'reCAPTCHA 失敗時要採取的操作',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 設定',
    'v3_site_key' => 'v3 網站金鑰',
    'enter_v3_site_key' => '輸入您的 reCAPTCHA v3 網站金鑰',
    'v3_secret_key' => 'v3 密鑰',
    'enter_v3_secret_key' => '輸入您的 reCAPTCHA v3 密鑰',
    'v3_score_threshold' => 'v3 分數閾值',
    'v3_score_hint' => '0.0 到 1.0 之間的值（越高越好）',
    'v3_preview' => 'v3 預覽',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 設定',
    'v2_site_key' => 'v2 網站金鑰',
    'enter_v2_site_key' => '輸入您的 reCAPTCHA v2 網站金鑰',
    'v2_secret_key' => 'v2 密鑰',
    'enter_v2_secret_key' => '輸入您的 reCAPTCHA v2 密鑰',
    'v2_preview' => 'v2 預覽',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => '外觀和訊息',
    'theme' => '主題',
    'theme_light' => '淺色',
    'theme_dark' => '深色',
    'size' => '大小',
    'size_normal' => '正常',
    'size_compact' => '緊湊',
    'badge_position' => '徽章位置',
    'badge_bottomright' => '右下角',
    'badge_bottomleft' => '左下角',
    'badge_inline' => '內嵌',
    
    /*
    * Common
    */
    'save' => '儲存',
    'saving' => '儲存中',
    'home' => '首頁',
    'settings' => '設定',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA 設定已成功更新！',
    
    /*
    * Error messages
    */
    'captcha_message' => '驗證碼驗證失敗。請重試。',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => '滿足條件時此欄位為必填',
    'select_captcha_version' => '請選擇驗證碼版本',
    'v3_site_key_required' => '需要 reCAPTCHA v3 網站金鑰',
    'v3_secret_key_required' => '需要 reCAPTCHA v3 密鑰',
    'v2_site_key_required' => '需要 reCAPTCHA v2 網站金鑰',
    'v2_secret_key_required' => '需要 reCAPTCHA v2 密鑰',
    'valid_recaptcha_site_key' => '請輸入有效的 reCAPTCHA 網站金鑰',
    'valid_recaptcha_secret_key' => '請輸入有效的 reCAPTCHA 密鑰',
    'score_threshold_required' => 'reCAPTCHA v3 需要分數閾值',
    'valid_number' => '請輸入有效數字',
    'complete_recaptcha_v3' => '請完成 reCAPTCHA v3。',
    'failed_generate_v3_token' => '無法產生 reCAPTCHA v3 權杖。',
    'complete_recaptcha_v2' => '請完成 reCAPTCHA v2。',
    'failed_generate_v2_token' => '無法產生 reCAPTCHA v2 權杖。',
    'settings_saved' => '設定已儲存。',
    'failed_save_settings' => '儲存設定失敗。請重試。',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => '密鑰或回應權杖無效',
    'captcha_verification_failed' => '驗證碼驗證失敗（分數/操作/主機名稱不相符）',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => '需要驗證碼版本',
    'captcha_version_in' => '所選驗證碼版本無效',
    'failover_action_required' => '需要故障轉移操作',
    'failover_action_in' => '所選故障轉移操作無效',
    'score_threshold_numeric' => '分數閾值必須是數字',
    'score_threshold_min' => '分數閾值至少為 0',
    'score_threshold_max' => '分數閾值不得超過 1',
    'theme_required' => '需要主題',
    'theme_in' => '所選主題無效',
    'size_required' => '需要大小',
    'size_in' => '所選大小無效',
    'badge_position_required' => '需要徽章位置',
    'badge_position_in' => '所選徽章位置無效',
];