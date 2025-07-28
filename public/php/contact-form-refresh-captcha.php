<?php

session_start();

include 'simple-php-captcha/simple-php-captcha.php';

$_SESSION['captcha'] = simple_php_captcha([
    'min_length' => 6,
    'max_length' => 6,
    'min_font_size' => 22,
    'max_font_size' => 22,
    'angle_max' => 3,
]);

$_SESSION['captchaCode'] = $_SESSION['captcha']['code'];

exit($_SESSION['captcha']['image_src']);
