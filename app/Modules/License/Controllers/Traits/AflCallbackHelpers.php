<?php

namespace App\Modules\License\Controllers\Traits;

use App\Modules\License\Models\LicenseCallback;
use App\Modules\License\Models\LicenseNotification;
use App\Modules\License\Models\LicenseReport;

trait AflCallbackHelpers
{
    /**
     * Build notification response with headers.
     * Original format: empty JSON body + notification_* headers.
     */
    protected function notificationResponse(
        string $notificationCase,
        array $data = [],
        ?int $product_id = null,
        ?string $client_email = null,
        ?string $license_code = null,
        ?string $root_url = null
    ) {
        $notification = LicenseNotification::first();
        $notificationText = $notification ? ($notification->{$notificationCase} ?? $notificationCase) : $notificationCase;
        $signature = $this->generateServerSignature($product_id, $root_url, $client_email, $license_code);

        return response()->json([])
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $notificationText)
            ->header('notification_server_signature', $signature)
            ->header('notification_data', json_encode($data));
    }

    /**
     * Generate server signature for callback verification.
     * Same algorithm as original: SHA256(server_ips + product_id + license_code + email + root_url + date).
     */
    protected function generateServerSignature(?int $product_id, ?string $root_url, ?string $client_email, ?string $license_code): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel($this->getRawDomain($rootUrl));

        if (empty($rootIps)) {
            return '';
        }

        return hash('sha256',
            implode('', $rootIps)
            .$product_id
            .$license_code
            .$client_email
            .$root_url
            .gmdate('Y-m-d')
        );
    }

    /**
     * Extract raw domain from URL (same as aflGetRawDomain in original).
     */
    protected function getRawDomain(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (empty($scheme)) {
            $url = 'http://'.$url;
        }

        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST) ?? '');
    }

    /**
     * Map validation error to notification case key.
     */
    protected function mapErrorToNotification(string $error): string
    {
        return match ($error) {
            'license_not_found' => 'notification_license_not_found',
            'license_suspended' => 'notification_license_suspended',
            'license_cancelled' => 'notification_license_cancelled',
            'license_expired' => 'notification_license_expired',
            'invalid_ip' => 'notification_invalid_ip',
            'invalid_domain' => 'notification_invalid_domain',
            'domain_required' => 'notification_domain_required',
            'domain_in_use' => 'notification_domain_in_use',
            default => 'notification_unknown_error',
        };
    }

    /**
     * Create license callback log.
     */
    protected function createCallback(int $productId, ?int $userId, string $licenseCode, string $ip, string $domain, int $status): void
    {
        LicenseCallback::create([
            'product_id' => $productId,
            'client_id' => $userId,
            'license_code' => $licenseCode,
            'callback_ip' => $ip,
            'callback_domain' => $domain,
            'callback_date_time' => now(),
            'callback_status' => $status,
        ]);
    }

    /**
     * Create license report.
     */
    protected function createReport(int $productId, ?int $userId, ?string $licenseCode, string $text, int $system): void
    {
        LicenseReport::create([
            'product_id' => $productId,
            'user_id' => $userId ?? 0,
            'license_code' => $licenseCode,
            'report_date_time' => now(),
            'report_text' => $text,
            'report_system' => $system,
            'report_status' => 1,
        ]);
    }
}
