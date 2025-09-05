<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => '캡차 설정',
    'captcha_configuration' => '캡차 구성',
    'captcha_version' => '캡차 버전',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 보이지 않음',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 체크박스',
    'select_captcha_type' => '사용할 reCAPTCHA 버전 선택',
    'failover_action' => '장애 조치 작업',
    'none' => '없음',
    'fallback_v2_checkbox' => 'reCAPTCHA v2 체크박스로 대체',
    'action_if_captcha_fails' => 'reCAPTCHA가 실패할 경우 수행할 작업',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'reCAPTCHA v3 설정',
    'v3_site_key' => 'v3 사이트 키',
    'enter_v3_site_key' => 'reCAPTCHA v3 사이트 키를 입력하세요',
    'v3_secret_key' => 'v3 비밀 키',
    'enter_v3_secret_key' => 'reCAPTCHA v3 비밀 키를 입력하세요',
    'v3_score_threshold' => 'v3 점수 임계값',
    'v3_score_hint' => '0.0에서 1.0 사이의 값 (높을수록 좋음)',
    'v3_preview' => 'v3 미리보기',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'reCAPTCHA v2 설정',
    'v2_site_key' => 'v2 사이트 키',
    'enter_v2_site_key' => 'reCAPTCHA v2 사이트 키를 입력하세요',
    'v2_secret_key' => 'v2 비밀 키',
    'enter_v2_secret_key' => 'reCAPTCHA v2 비밀 키를 입력하세요',
    'v2_preview' => 'v2 미리보기',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => '외관 및 메시지',
    'theme' => '테마',
    'theme_light' => '밝은',
    'theme_dark' => '어두운',
    'size' => '크기',
    'size_normal' => '일반',
    'size_compact' => '작은',
    'badge_position' => '배지 위치',
    'badge_bottomright' => '오른쪽 아래',
    'badge_bottomleft' => '왼쪽 아래',
    'badge_inline' => '인라인',
    'error_message' => '오류 메시지',
    
    /*
    * Common
    */
    'save' => '저장',
    'saving' => '저장 중',
    'home' => '홈',
    'settings' => '설정',
    
    /*
    * Success messages
    */
    'captcha_settings_updated' => 'reCAPTCHA 설정이 성공적으로 업데이트되었습니다!',
    
    /*
    * Error messages
    */
    'captcha_message' => '캡차 인증에 실패했습니다. 다시 시도해 주세요.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => '조건이 충족될 때 이 필드는 필수입니다',
    'select_captcha_version' => '캡차 버전을 선택해 주세요',
    'v3_site_key_required' => 'reCAPTCHA v3 사이트 키가 필요합니다',
    'v3_secret_key_required' => 'reCAPTCHA v3 비밀 키가 필요합니다',
    'v2_site_key_required' => 'reCAPTCHA v2 사이트 키가 필요합니다',
    'v2_secret_key_required' => 'reCAPTCHA v2 비밀 키가 필요합니다',
    'valid_recaptcha_site_key' => '유효한 reCAPTCHA 사이트 키를 입력해 주세요',
    'valid_recaptcha_secret_key' => '유효한 reCAPTCHA 비밀 키를 입력해 주세요',
    'score_threshold_required' => 'reCAPTCHA v3에는 점수 임계값이 필요합니다',
    'valid_number' => '유효한 숫자를 입력해 주세요',
    'complete_recaptcha_v3' => 'reCAPTCHA v3을 완료해 주세요.',
    'failed_generate_v3_token' => 'reCAPTCHA v3 토큰 생성에 실패했습니다.',
    'complete_recaptcha_v2' => 'reCAPTCHA v2를 완료해 주세요.',
    'failed_generate_v2_token' => 'reCAPTCHA v2 토큰 생성에 실패했습니다.',
    'settings_saved' => '설정이 저장되었습니다.',
    'failed_save_settings' => '설정 저장에 실패했습니다. 다시 시도해 주세요.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => '비밀 키 또는 응답 토큰이 유효하지 않습니다',
    'captcha_verification_failed' => '캡차 인증에 실패했습니다 (점수/작업/호스트명 불일치)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => '캡차 버전이 필요합니다',
    'captcha_version_in' => '선택한 캡차 버전이 유효하지 않습니다',
    'failover_action_required' => '장애 조치 작업이 필요합니다',
    'failover_action_in' => '선택한 장애 조치 작업이 유효하지 않습니다',
    'score_threshold_numeric' => '점수 임계값은 숫자여야 합니다',
    'score_threshold_min' => '점수 임계값은 최소 0이어야 합니다',
    'score_threshold_max' => '점수 임계값은 1을 초과할 수 없습니다',
    'error_message_required' => '오류 메시지가 필요합니다',
    'error_message_max' => '오류 메시지는 255자를 초과할 수 없습니다',
    'theme_required' => '테마가 필요합니다',
    'theme_in' => '선택한 테마가 유효하지 않습니다',
    'size_required' => '크기가 필요합니다',
    'size_in' => '선택한 크기가 유효하지 않습니다',
    'badge_position_required' => '배지 위치가 필요합니다',
    'badge_position_in' => '선택한 배지 위치가 유효하지 않습니다',
];