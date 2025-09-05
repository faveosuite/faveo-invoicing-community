<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Captcha Settings',
    'captcha_configuration' => 'Captcha Configuration',
    'captcha_version' => 'Captcha Version',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisible',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Checkbox',
    'select_captcha_type' => 'Select which version of reCAPTCHA to use',
    'failover_action' => 'Failover Action',
    'none' => 'None',
    'fallback_v2_checkbox' => 'Fallback to reCAPTCHA v2 Checkbox',
    'action_if_captcha_fails' => 'Action to take if reCAPTCHA fails',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 Settings',
    'v3_site_key' => 'v3 Site Key',
    'enter_v3_site_key' => 'Enter your reCAPTCHA v3 site key',
    'v3_secret_key' => 'v3 Secret Key',
    'enter_v3_secret_key' => 'Enter your reCAPTCHA v3 secret key',
    'v3_score_threshold' => 'v3 Score Threshold',
    'v3_score_hint' => 'Value between 0.0 and 1.0 (higher is better)',
    'v3_preview' => 'v3 Preview',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 Settings',
    'v2_site_key' => 'v2 Site Key',
    'enter_v2_site_key' => 'Enter your reCAPTCHA v2 site key',
    'v2_secret_key' => 'v2 Secret Key',
    'enter_v2_secret_key' => 'Enter your reCAPTCHA v2 secret key',
    'v2_preview' => 'v2 Preview',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Appearance & Messages',
    'theme' => 'Theme',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',
    'size' => 'Size',
    'size_normal' => 'Normal',
    'size_compact' => 'Compact',
    'badge_position' => 'Badge Position',
    'badge_bottomright' => 'Bottom Right',
    'badge_bottomleft' => 'Bottom Left',
    'badge_inline' => 'Inline',
    
    /*
    * Common
    */
    'save' => 'Save',
    'saving' => 'Saving',
    'home' => 'Home',
    'settings' => 'Settings',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA settings updated successfully!',
    
    /*
    * Error messages
    */
    'captcha_message' => 'Captcha verification failed. Please try again.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'This field is required when the condition is met',
    'select_captcha_version' => 'Please select a captcha version',
    'v3_site_key_required' => 'reCAPTCHA v3 site key is required',
    'v3_secret_key_required' => 'reCAPTCHA v3 secret key is required',
    'v2_site_key_required' => 'reCAPTCHA v2 site key is required',
    'v2_secret_key_required' => 'reCAPTCHA v2 secret key is required',
    'valid_recaptcha_site_key' => 'Please enter a valid reCAPTCHA site key',
    'valid_recaptcha_secret_key' => 'Please enter a valid reCAPTCHA secret key',
    'score_threshold_required' => 'Score threshold is required for reCAPTCHA v3',
    'valid_number' => 'Please enter a valid number',
    'complete_recaptcha_v3' => 'Please complete the reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Failed to generate reCAPTCHA v3 token.',
    'complete_recaptcha_v2' => 'Please complete the reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Failed to generate reCAPTCHA v2 token.',
    'settings_saved' => 'Settings saved.',
    'failed_save_settings' => 'Failed to save settings. Please try again.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Secret key or response token is invalid',
    'captcha_verification_failed' => 'Captcha verification failed (score/action/hostname mismatch)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'Captcha version is required',
    'captcha_version_in' => 'Selected captcha version is invalid',
    'failover_action_required' => 'Failover action is required',
    'failover_action_in' => 'Selected failover action is invalid',
    'score_threshold_numeric' => 'Score threshold must be a number',
    'score_threshold_min' => 'Score threshold must be at least 0',
    'score_threshold_max' => 'Score threshold must not be greater than 1',
    'theme_required' => 'Theme is required',
    'theme_in' => 'Selected theme is invalid',
    'size_required' => 'Size is required',
    'size_in' => 'Selected size is invalid',
    'badge_position_required' => 'Badge position is required',
    'badge_position_in' => 'Selected badge position is invalid',
];