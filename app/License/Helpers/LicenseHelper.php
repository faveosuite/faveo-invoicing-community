<?php

namespace App\License\Helpers;

use App\License\Models\LicenseReport;
use DateTime;
use Illuminate\Support\Facades\Auth;

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

        return (bool) preg_match('/^[a-z0-9-.]+\.([a-z\.]{2,7}|localhost)$/', strtolower($url));
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

        return str_ireplace('www.', '', (string) (parse_url($url, PHP_URL_HOST) ?? ''));
    }

    /**
     * Log an admin/system action to System Reports, attributed to the signed-in admin
     * (none for automatic actions such as auto-bans, which then show as "System").
     */
    public static function logAdminReport(string $reportText, int $reportStatus): void
    {
        if ($reportText === '' || $reportText === '0') {
            return;
        }

        LicenseReport::create([
            'user_id' => Auth::id(),
            'report_date_time' => now(),
            'report_text' => $reportText,
            'report_system' => 1,
            'report_status' => $reportStatus,
        ]);
    }

    public static function formatClient(?string $licenseCode, ?string $clientEmail): string
    {
        if (! in_array($licenseCode, [null, '', '0'], strict: true)) {
            return (string) $licenseCode;
        }

        if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return (string) $clientEmail;
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
