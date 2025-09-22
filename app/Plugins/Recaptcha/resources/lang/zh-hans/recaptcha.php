<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => '验证码设置',
    'captcha_configuration' => '验证码配置',
    'captcha_version' => '验证码版本',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 隐藏',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 复选框',
    'select_captcha_type' => '选择要使用的 reCAPTCHA 版本',
    'failover_action' => '故障转移操作',
    'none' => '无',
    'fallback_v2_checkbox' => '回退到 reCAPTCHA v2 复选框',
    'action_if_captcha_fails' => 'reCAPTCHA 失败时采取的操作',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 设置',
    'v3_site_key' => 'v3 网站密钥',
    'enter_v3_site_key' => '输入您的 reCAPTCHA v3 网站密钥',
    'v3_secret_key' => 'v3 密钥',
    'enter_v3_secret_key' => '输入您的 reCAPTCHA v3 密钥',
    'v3_score_threshold' => 'v3 分数阈值',
    'v3_score_hint' => '0.0 到 1.0 之间的值（越高越好）',
    'v3_preview' => 'v3 预览',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 设置',
    'v2_site_key' => 'v2 网站密钥',
    'enter_v2_site_key' => '输入您的 reCAPTCHA v2 网站密钥',
    'v2_secret_key' => 'v2 密钥',
    'enter_v2_secret_key' => '输入您的 reCAPTCHA v2 密钥',
    'v2_preview' => 'v2 预览',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => '外观和消息',
    'theme' => '主题',
    'theme_light' => '浅色',
    'theme_dark' => '深色',
    'size' => '尺寸',
    'size_normal' => '正常',
    'size_compact' => '紧凑',
    'badge_position' => '徽章位置',
    'badge_bottomright' => '右下角',
    'badge_bottomleft' => '左下角',
    'badge_inline' => '内联',
    
    /*
    * Common
    */
    'save' => '保存',
    'saving' => '正在保存',
    'home' => '首页',
    'settings' => '设置',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA 设置已成功更新！',
    
    /*
    * Error messages
    */
    'captcha_message' => '验证码验证失败。请重试。',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => '满足条件时此字段为必填项',
    'select_captcha_version' => '请选择验证码版本',
    'v3_site_key_required' => '需要 reCAPTCHA v3 网站密钥',
    'v3_secret_key_required' => '需要 reCAPTCHA v3 密钥',
    'v2_site_key_required' => '需要 reCAPTCHA v2 网站密钥',
    'v2_secret_key_required' => '需要 reCAPTCHA v2 密钥',
    'valid_recaptcha_site_key' => '请输入有效的 reCAPTCHA 网站密钥',
    'valid_recaptcha_secret_key' => '请输入有效的 reCAPTCHA 密钥',
    'score_threshold_required' => 'reCAPTCHA v3 需要分数阈值',
    'valid_number' => '请输入有效数字',
    'complete_recaptcha_v3' => '请完成 reCAPTCHA v3。',
    'failed_generate_v3_token' => '无法生成 reCAPTCHA v3 令牌。',
    'complete_recaptcha_v2' => '请完成 reCAPTCHA v2。',
    'failed_generate_v2_token' => '无法生成 reCAPTCHA v2 令牌。',
    'settings_saved' => '设置已保存。',
    'failed_save_settings' => '保存设置失败。请重试。',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => '密钥或响应令牌无效',
    'captcha_verification_failed' => '验证码验证失败（分数/操作/主机名不匹配）',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => '需要验证码版本',
    'captcha_version_in' => '所选验证码版本无效',
    'failover_action_required' => '需要故障转移操作',
    'failover_action_in' => '所选故障转移操作无效',
    'score_threshold_numeric' => '分数阈值必须是数字',
    'score_threshold_min' => '分数阈值至少为 0',
    'score_threshold_max' => '分数阈值不得超过 1',
    'theme_required' => '需要主题',
    'theme_in' => '所选主题无效',
    'size_required' => '需要尺寸',
    'size_in' => '所选尺寸无效',
    'badge_position_required' => '需要徽章位置',
    'badge_position_in' => '所选徽章位置无效',
];