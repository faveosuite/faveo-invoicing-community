<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Cài đặt reCAPTCHA',
    'captcha_configuration' => 'Cấu hình reCAPTCHA',
    'captcha_version' => 'Phiên bản reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 vô hình',
    'recaptcha_v2_checkbox' => 'Hộp kiểm reCAPTCHA v2',
    'select_captcha_type' => 'Chọn phiên bản reCAPTCHA để sử dụng',
    'failover_action' => 'Hành động chuyển đổi dự phòng',
    'none' => 'Không có',
    'fallback_v2_checkbox' => 'Quay lại hộp kiểm reCAPTCHA v2',
    'action_if_captcha_fails' => 'Hành động cần thực hiện nếu reCAPTCHA không thành công',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Cài đặt reCAPTCHA v3',
    'v3_site_key' => 'Khóa trang web v3',
    'enter_v3_site_key' => 'Nhập khóa trang web reCAPTCHA v3 của bạn',
    'v3_secret_key' => 'Khóa bí mật v3',
    'enter_v3_secret_key' => 'Nhập khóa bí mật reCAPTCHA v3 của bạn',
    'v3_score_threshold' => 'Ngưỡng điểm v3',
    'v3_score_hint' => 'Giá trị từ 0,0 đến 1,0 (càng cao càng tốt)',
    'v3_preview' => 'Xem trước v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Cài đặt reCAPTCHA v2',
    'v2_site_key' => 'Khóa trang web v2',
    'enter_v2_site_key' => 'Nhập khóa trang web reCAPTCHA v2 của bạn',
    'v2_secret_key' => 'Khóa bí mật v2',
    'enter_v2_secret_key' => 'Nhập khóa bí mật reCAPTCHA v2 của bạn',
    'v2_preview' => 'Xem trước v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Giao diện',
    'theme' => 'Chủ đề',
    'theme_light' => 'Sáng',
    'theme_dark' => 'Tối',
    'size' => 'Kích thước',
    'size_normal' => 'Bình thường',
    'size_compact' => 'Nhỏ gọn',
    'badge_position' => 'Vị trí huy hiệu',
    'badge_bottomright' => 'Dưới cùng bên phải',
    'badge_bottomleft' => 'Dưới cùng bên trái',
    'badge_inline' => 'Nội tuyến',

    /*
    * Common
    */
    'save' => 'Lưu',
    'saving' => 'Đang lưu',
    'home' => 'Trang chủ',
    'settings' => 'Cài đặt',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Cài đặt reCAPTCHA đã được cập nhật thành công!',

    /*
    * Error messages
    */
    'captcha_message' => 'Xác minh reCAPTCHA không thành công. Vui lòng thử lại.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Trường này là bắt buộc khi điều kiện được đáp ứng',
    'select_captcha_version' => 'Vui lòng chọn phiên bản reCAPTCHA',
    'v3_site_key_required' => 'Yêu cầu khóa trang web reCAPTCHA v3',
    'v3_secret_key_required' => 'Yêu cầu khóa bí mật reCAPTCHA v3',
    'v2_site_key_required' => 'Yêu cầu khóa trang web reCAPTCHA v2',
    'v2_secret_key_required' => 'Yêu cầu khóa bí mật reCAPTCHA v2',
    'valid_recaptcha_site_key' => 'Vui lòng nhập khóa trang web reCAPTCHA hợp lệ',
    'valid_recaptcha_secret_key' => 'Vui lòng nhập khóa bí mật reCAPTCHA hợp lệ',
    'score_threshold_required' => 'Yêu cầu ngưỡng điểm cho reCAPTCHA v3',
    'valid_number' => 'Vui lòng nhập một số hợp lệ',
    'complete_recaptcha_v3' => 'Không thể tạo mã thông báo reCAPTCHA. Vui lòng xác minh rằng khóa trang web được định cấu hình chính xác và hợp lệ.',
    'failed_generate_v3_token' => 'Không thể tạo mã thông báo reCAPTCHA. Vui lòng xác minh rằng khóa trang web được định cấu hình chính xác và hợp lệ.',
    'complete_recaptcha_v2' => 'Vui lòng hoàn thành reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Không thể tạo mã thông báo reCAPTCHA v2.',
    'settings_saved' => 'Đã lưu cài đặt.',
    'failed_save_settings' => 'Không thể lưu cài đặt. Vui lòng thử lại.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Khóa bí mật hoặc mã thông báo phản hồi không hợp lệ',
    'captcha_verification_failed' => 'Xác minh reCAPTCHA không thành công (không khớp điểm/hành động/tên máy chủ)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'Yêu cầu phiên bản reCAPTCHA',
    'captcha_version_in' => 'Phiên bản reCAPTCHA đã chọn không hợp lệ',
    'failover_action_required' => 'Yêu cầu hành động chuyển đổi dự phòng',
    'failover_action_in' => 'Hành động chuyển đổi dự phòng đã chọn không hợp lệ',
    'score_threshold_numeric' => 'Ngưỡng điểm phải là một số',
    'score_threshold_min' => 'Ngưỡng điểm phải ít nhất là 0',
    'score_threshold_max' => 'Ngưỡng điểm không được lớn hơn 1',
    'theme_required' => 'Yêu cầu chủ đề',
    'theme_in' => 'Chủ đề đã chọn không hợp lệ',
    'size_required' => 'Yêu cầu kích thước',
    'size_in' => 'Kích thước đã chọn không hợp lệ',
    'badge_position_required' => 'Yêu cầu vị trí huy hiệu',
    'badge_position_in' => 'Vị trí huy hiệu đã chọn không hợp lệ',
];
