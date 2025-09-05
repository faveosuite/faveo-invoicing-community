<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'キャプチャ設定',
    'captcha_configuration' => 'キャプチャ設定',
    'captcha_version' => 'キャプチャバージョン',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 非表示',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 チェックボックス',
    'select_captcha_type' => '使用するreCAPTCHAのバージョンを選択',
    'failover_action' => 'フェイルオーバーアクション',
    'none' => 'なし',
    'fallback_v2_checkbox' => 'reCAPTCHA v2 チェックボックスにフォールバック',
    'action_if_captcha_fails' => 'reCAPTCHAが失敗した場合のアクション',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 設定',
    'v3_site_key' => 'v3 サイトキー',
    'enter_v3_site_key' => 'reCAPTCHA v3 サイトキーを入力',
    'v3_secret_key' => 'v3 シークレットキー',
    'enter_v3_secret_key' => 'reCAPTCHA v3 シークレットキーを入力',
    'v3_score_threshold' => 'v3 スコア閾値',
    'v3_score_hint' => '0.0から1.0の間の値（高いほど良い）',
    'v3_preview' => 'v3 プレビュー',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 設定',
    'v2_site_key' => 'v2 サイトキー',
    'enter_v2_site_key' => 'reCAPTCHA v2 サイトキーを入力',
    'v2_secret_key' => 'v2 シークレットキー',
    'enter_v2_secret_key' => 'reCAPTCHA v2 シークレットキーを入力',
    'v2_preview' => 'v2 プレビュー',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => '外観とメッセージ',
    'theme' => 'テーマ',
    'theme_light' => 'ライト',
    'theme_dark' => 'ダーク',
    'size' => 'サイズ',
    'size_normal' => '通常',
    'size_compact' => 'コンパクト',
    'badge_position' => 'バッジの位置',
    'badge_bottomright' => '右下',
    'badge_bottomleft' => '左下',
    'badge_inline' => 'インライン',
    'error_message' => 'エラーメッセージ',
    
    /*
    * Common
    */
    'save' => '保存',
    'saving' => '保存中',
    'home' => 'ホーム',
    'settings' => '設定',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA設定が正常に更新されました！',
    
    /*
    * Error messages
    */
    'captcha_message' => 'キャプチャ認証に失敗しました。もう一度お試しください。',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => '条件が満たされた場合、このフィールドは必須です',
    'select_captcha_version' => 'キャプチャバージョンを選択してください',
    'v3_site_key_required' => 'reCAPTCHA v3 サイトキーが必要です',
    'v3_secret_key_required' => 'reCAPTCHA v3 シークレットキーが必要です',
    'v2_site_key_required' => 'reCAPTCHA v2 サイトキーが必要です',
    'v2_secret_key_required' => 'reCAPTCHA v2 シークレットキーが必要です',
    'valid_recaptcha_site_key' => '有効なreCAPTCHAサイトキーを入力してください',
    'valid_recaptcha_secret_key' => '有効なreCAPTCHAシークレットキーを入力してください',
    'score_threshold_required' => 'reCAPTCHA v3にはスコア閾値が必要です',
    'valid_number' => '有効な数値を入力してください',
    'complete_recaptcha_v3' => 'reCAPTCHA v3を完了してください。',
    'failed_generate_v3_token' => 'reCAPTCHA v3トークンの生成に失敗しました。',
    'complete_recaptcha_v2' => 'reCAPTCHA v2を完了してください。',
    'failed_generate_v2_token' => 'reCAPTCHA v2トークンの生成に失敗しました。',
    'settings_saved' => '設定が保存されました。',
    'failed_save_settings' => '設定の保存に失敗しました。もう一度お試しください。',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'シークレットキーまたはレスポントークンが無効です',
    'captcha_verification_failed' => 'キャプチャ認証に失敗しました（スコア/アクション/ホスト名の不一致）',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'キャプチャバージョンが必要です',
    'captcha_version_in' => '選択されたキャプチャバージョンは無効です',
    'failover_action_required' => 'フェイルオーバーアクションが必要です',
    'failover_action_in' => '選択されたフェイルオーバーアクションは無効です',
    'score_threshold_numeric' => 'スコア閾値は数値でなければなりません',
    'score_threshold_min' => 'スコア閾値は少なくとも0でなければなりません',
    'score_threshold_max' => 'スコア閾値は1を超えてはなりません',
    'error_message_required' => 'エラーメッセージが必要です',
    'error_message_max' => 'エラーメッセージは255文字を超えてはなりません',
    'theme_required' => 'テーマが必要です',
    'theme_in' => '選択されたテーマは無効です',
    'size_required' => 'サイズが必要です',
    'size_in' => '選択されたサイズは無効です',
    'badge_position_required' => 'バッジの位置が必要です',
    'badge_position_in' => '選択されたバッジの位置は無効です',
];