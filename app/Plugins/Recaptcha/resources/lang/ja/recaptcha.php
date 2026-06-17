<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'reCAPTCHA設定',
    'captcha_configuration' => 'reCAPTCHA設定',
    'captcha_version' => 'reCAPTCHAバージョン',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisible',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2チェックボックス',
    'select_captcha_type' => '使用するreCAPTCHAのバージョンを選択してください',
    'failover_action' => 'フェイルオーバーアクション',
    'none' => 'なし',
    'fallback_v2_checkbox' => 'reCAPTCHA v2チェックボックスにフォールバックします',
    'action_if_captcha_fails' => 'reCAPTCHAが失敗した場合のアクション',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3設定',
    'v3_site_key' => 'v3サイトキー',
    'enter_v3_site_key' => 'reCAPTCHA v3サイトキーを入力してください',
    'v3_secret_key' => 'v3シークレットキー',
    'enter_v3_secret_key' => 'reCAPTCHA v3シークレットキーを入力してください',
    'v3_score_threshold' => 'v3スコアしきい値',
    'v3_score_hint' => '0.0から1.0までの値（高いほど良い）',
    'v3_preview' => 'v3プレビュー',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2設定',
    'v2_site_key' => 'v2サイトキー',
    'enter_v2_site_key' => 'reCAPTCHA v2サイトキーを入力してください',
    'v2_secret_key' => 'v2シークレットキー',
    'enter_v2_secret_key' => 'reCAPTCHA v2シークレットキーを入力してください',
    'v2_preview' => 'v2プレビュー',

    /*
    * Appearance
    */
    'appearance_messages' => '外観',
    'theme' => 'テーマ',
    'theme_light' => 'ライト',
    'theme_dark' => 'ダーク',
    'size' => 'サイズ',
    'size_normal' => 'ノーマル',
    'size_compact' => 'コンパクト',
    'badge_position' => 'バッジの位置',
    'badge_bottomright' => '右下',
    'badge_bottomleft' => '左下',
    'badge_inline' => 'インライン',

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
    'captcha_message' => 'reCAPTCHAの検証に失敗しました。もう一度お試しください。',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => '条件が満たされた場合、このフィールドは必須です',
    'select_captcha_version' => 'reCAPTCHAのバージョンを選択してください',
    'v3_site_key_required' => 'reCAPTCHA v3サイトキーが必要です',
    'v3_secret_key_required' => 'reCAPTCHA v3シークレットキーが必要です',
    'v2_site_key_required' => 'reCAPTCHA v2サイトキーが必要です',
    'v2_secret_key_required' => 'reCAPTCHA v2シークレットキーが必要です',
    'valid_recaptcha_site_key' => '有効なreCAPTCHAサイトキーを入力してください',
    'valid_recaptcha_secret_key' => '有効なreCAPTCHAシークレットキーを入力してください',
    'score_threshold_required' => 'reCAPTCHA v3にはスコアしきい値が必要です',
    'valid_number' => '有効な数値を入力してください',
    'complete_recaptcha_v3' => 'reCAPTCHAトークンの生成に失敗しました。サイトキーが正しく設定され、有効であることを確認してください。',
    'failed_generate_v3_token' => 'reCAPTCHAトークンの生成に失敗しました。サイトキーが正しく設定され、有効であることを確認してください。',
    'complete_recaptcha_v2' => 'reCAPTCHA v2を完了してください。',
    'failed_generate_v2_token' => 'reCAPTCHA v2トークンの生成に失敗しました。',
    'settings_saved' => '設定が保存されました。',
    'failed_save_settings' => '設定の保存に失敗しました。もう一度お試しください。',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'シークレットキーまたは応答トークンが無効です',
    'captcha_verification_failed' => 'reCAPTCHAの検証に失敗しました（スコア/アクション/ホスト名の不一致）',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'reCAPTCHAバージョンが必要です',
    'captcha_version_in' => '選択したreCAPTCHAバージョンは無効です',
    'failover_action_required' => 'フェイルオーバーアクションが必要です',
    'failover_action_in' => '選択したフェイルオーバーアクションは無効です',
    'score_threshold_numeric' => 'スコアしきい値は数値である必要があります',
    'score_threshold_min' => 'スコアしきい値は少なくとも0である必要があります',
    'score_threshold_max' => 'スコアしきい値は1を超えてはなりません',
    'theme_required' => 'テーマが必要です',
    'theme_in' => '選択したテーマは無効です',
    'size_required' => 'サイズが必要です',
    'size_in' => '選択したサイズは無効です',
    'badge_position_required' => 'バッジの位置が必要です',
    'badge_position_in' => '選択したバッジの位置は無効です',
];
