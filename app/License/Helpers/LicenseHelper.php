<?php

namespace App\License\Helpers;

use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class LicenseHelper
{
    public static function validateIntegerValue(mixed $number, int $min = 1, int $max = 999999999): bool
    {
        if (is_float($number)) {
            return false;
        }

        return filter_var($number, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]) !== false;
    }

    public static function verifyDateTime(?string $datetime, string $format): bool
    {
        if (in_array($datetime, [null, '', '0'], strict: true) || ($format === '' || $format === '0')) {
            return false;
        }

        $dt = DateTime::createFromFormat($format, $datetime);
        $errors = DateTime::getLastErrors();

        return $dt && empty($errors['warning_count']);
    }

    public static function validateRawDomain(?string $url): bool
    {
        if (in_array($url, [null, '', '0'], strict: true)) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9-.]+\.[a-z\.]{2,7}$/', strtolower($url));
    }

    public static function getRawDomain(?string $url): string
    {
        if (in_array($url, [null, '', '0'], strict: true)) {
            return '';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (empty($scheme)) {
            $url = 'http://'.$url;
        }

        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST) ?? '');
    }

    public static function logAdminReport(string $reportText, mixed $accountId, int $reportSystem, int $reportStatus): int
    {
        if ($reportText === '' || $reportText === '0' || ! self::validateIntegerValue($reportSystem, 0, 1)) {
            return 0;
        }

        try {
            DB::table('afl_reports')->insertOrIgnore([
                'account_id' => $accountId,
                'report_date_time' => date('Y-m-d H:i:s'),
                'report_text' => $reportText,
                'report_system' => $reportSystem,
                'report_status' => $reportStatus,
            ]);

            return 1;
        } catch (Exception) {
            return 0;
        }
    }

    public static function formatClient(?string $licenseCode, ?string $clientEmail): string
    {
        if (! in_array($licenseCode, [null, '', '0'], strict: true)) {
            return $licenseCode;
        }

        if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return $clientEmail;
        }

        return 'Unknown Client';
    }

    public static function statusFormatter(mixed $status): mixed
    {
        if (strtolower($status) === 'active') {
            return 1;
        }

        if (strtolower($status) === 'inactive') {
            return 0;
        }

        return $status;
    }

    public static function successErrorFormatter(mixed $status): mixed
    {
        if (strtolower($status) === 'success') {
            return 1;
        }

        if (strtolower($status) === 'error') {
            return 0;
        }

        return $status;
    }
}
