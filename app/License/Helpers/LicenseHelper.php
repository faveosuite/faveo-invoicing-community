<?php

namespace App\License\Helpers;

use Illuminate\Support\Facades\DB;

class LicenseHelper
{
    public static function validateIntegerValue($number, int $min = 1, int $max = 999999999): bool
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
        if (empty($datetime) || empty($format)) {
            return false;
        }

        $dt = \DateTime::createFromFormat($format, $datetime);
        $errors = \DateTime::getLastErrors();

        return $dt && empty($errors['warning_count']);
    }

    public static function logAdminReport(string $reportText, $accountId, int $reportSystem, int $reportStatus): int
    {
        if (empty($reportText) || ! self::validateIntegerValue($reportSystem, 0, 1)) {
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
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function formatClient(?string $licenseCode, ?string $clientEmail): string
    {
        if (! empty($licenseCode)) {
            return $licenseCode;
        }

        if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            return $clientEmail;
        }

        return 'Unknown Client';
    }

    public static function statusFormatter($status)
    {
        if (strtolower($status) == 'active') {
            return 1;
        }
        if (strtolower($status) == 'inactive') {
            return 0;
        }

        return $status;
    }

    public static function successErrorFormatter($status)
    {
        if (strtolower($status) == 'success') {
            return 1;
        }
        if (strtolower($status) == 'error') {
            return 0;
        }

        return $status;
    }

    public static function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '----';
        }

        return getTimeInLoggedInUserTimeZone($date, 'M j, Y');
    }

    public static function formatDatetime(?string $date): string
    {
        if (empty($date)) {
            return '----';
        }

        return getTimeInLoggedInUserTimeZone($date, 'M j, Y, g:i a');
    }
}
