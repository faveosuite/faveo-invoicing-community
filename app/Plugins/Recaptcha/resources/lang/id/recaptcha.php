<?php

declare(strict_types=1);

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Pengaturan reCAPTCHA',
    'captcha_configuration' => 'Konfigurasi reCAPTCHA',
    'captcha_version' => 'Versi reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Tak Terlihat',
    'recaptcha_v2_checkbox' => 'Kotak Centang reCAPTCHA v2',
    'select_captcha_type' => 'Pilih versi reCAPTCHA yang akan digunakan',
    'failover_action' => 'Tindakan Failover',
    'none' => 'Tidak ada',
    'fallback_v2_checkbox' => 'Kembali ke Kotak Centang reCAPTCHA v2',
    'action_if_captcha_fails' => 'Tindakan yang harus diambil jika reCAPTCHA gagal',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Pengaturan reCAPTCHA v3',
    'v3_site_key' => 'Kunci Situs v3',
    'enter_v3_site_key' => 'Masukkan kunci situs reCAPTCHA v3 Anda',
    'v3_secret_key' => 'Kunci Rahasia v3',
    'enter_v3_secret_key' => 'Masukkan kunci rahasia reCAPTCHA v3 Anda',
    'v3_score_threshold' => 'Ambang Skor v3',
    'v3_score_hint' => 'Nilai antara 0,0 dan 1,0 (lebih tinggi lebih baik)',
    'v3_preview' => 'Pratinjau v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Pengaturan reCAPTCHA v2',
    'v2_site_key' => 'Kunci Situs v2',
    'enter_v2_site_key' => 'Masukkan kunci situs reCAPTCHA v2 Anda',
    'v2_secret_key' => 'Kunci Rahasia v2',
    'enter_v2_secret_key' => 'Masukkan kunci rahasia reCAPTCHA v2 Anda',
    'v2_preview' => 'Pratinjau v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Penampilan',
    'theme' => 'Tema',
    'theme_light' => 'Terang',
    'theme_dark' => 'Gelap',
    'size' => 'Ukuran',
    'size_normal' => 'Normal',
    'size_compact' => 'Kompak',
    'badge_position' => 'Posisi Lencana',
    'badge_bottomright' => 'Kanan Bawah',
    'badge_bottomleft' => 'Kiri Bawah',
    'badge_inline' => 'Sejajar',

    /*
    * Common
    */
    'save' => 'Simpan',
    'saving' => 'Menyimpan',
    'home' => 'Beranda',
    'settings' => 'Pengaturan',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Pengaturan reCAPTCHA berhasil diperbarui!',

    /*
    * Error messages
    */
    'captcha_message' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Bidang ini wajib diisi bila kondisi terpenuhi',
    'select_captcha_version' => 'Silakan pilih versi reCAPTCHA',
    'v3_site_key_required' => 'Kunci situs reCAPTCHA v3 diperlukan',
    'v3_secret_key_required' => 'Kunci rahasia reCAPTCHA v3 diperlukan',
    'v2_site_key_required' => 'Kunci situs reCAPTCHA v2 diperlukan',
    'v2_secret_key_required' => 'Kunci rahasia reCAPTCHA v2 diperlukan',
    'valid_recaptcha_site_key' => 'Silakan masukkan kunci situs reCAPTCHA yang valid',
    'valid_recaptcha_secret_key' => 'Silakan masukkan kunci rahasia reCAPTCHA yang valid',
    'score_threshold_required' => 'Ambang skor diperlukan untuk reCAPTCHA v3',
    'valid_number' => 'Silakan masukkan nomor yang valid',
    'complete_recaptcha_v3' => 'Gagal membuat token reCAPTCHA. Harap verifikasi bahwa kunci situs dikonfigurasi dengan benar dan valid.',
    'failed_generate_v3_token' => 'Gagal membuat token reCAPTCHA. Harap verifikasi bahwa kunci situs dikonfigurasi dengan benar dan valid.',
    'complete_recaptcha_v2' => 'Silakan selesaikan reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Gagal membuat token reCAPTCHA v2.',
    'settings_saved' => 'Pengaturan disimpan.',
    'failed_save_settings' => 'Gagal menyimpan pengaturan. Silakan coba lagi.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Kunci rahasia atau token respons tidak valid',
    'captcha_verification_failed' => 'Verifikasi reCAPTCHA gagal (skor/tindakan/nama host tidak cocok)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'Versi reCAPTCHA diperlukan',
    'captcha_version_in' => 'Versi reCAPTCHA yang dipilih tidak valid',
    'failover_action_required' => 'Tindakan failover diperlukan',
    'failover_action_in' => 'Tindakan failover yang dipilih tidak valid',
    'score_threshold_numeric' => 'Ambang skor harus berupa angka',
    'score_threshold_min' => 'Ambang skor harus minimal 0',
    'score_threshold_max' => 'Ambang skor tidak boleh lebih besar dari 1',
    'theme_required' => 'Tema diperlukan',
    'theme_in' => 'Tema yang dipilih tidak valid',
    'size_required' => 'Ukuran diperlukan',
    'size_in' => 'Ukuran yang dipilih tidak valid',
    'badge_position_required' => 'Posisi lencana diperlukan',
    'badge_position_in' => 'Posisi lencana yang dipilih tidak valid',
];
