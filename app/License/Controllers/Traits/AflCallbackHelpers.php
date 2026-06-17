<?php

namespace App\License\Controllers\Traits;

use App\License\Models\LicenseCallback;
use App\License\Models\LicenseNotification;
use App\License\Models\LicenseReport;

trait AflCallbackHelpers
{
    /**
     * Build notification response with headers.
     * Original format: empty JSON body + notification_* headers.
     * Matches returnServerNotification() from original:
     * - Fetches notification text from DB and replaces placeholders
     * - Only sends notification_data when notification_case is 'notification_license_ok'.
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

        // Replace placeholders in notification text (matching original)
        $ip = request()->ip();
        $placeholders = ['%ROOT_URL%', '%IP_ADDRESS%', '%CLIENT_EMAIL%', '%LICENSE_CODE%', '%PRODUCT_ID%'];
        $replacements = [$root_url ?? '', $ip, $client_email ?? '', $license_code ?? '', $product_id ?? ''];
        $notificationText = str_ireplace($placeholders, $replacements, $notificationText);

        $signature = $this->generateServerSignature($product_id, $root_url, $client_email, $license_code);

        // Original only returns notification_data when everything is OK
        $responseData = ($notificationCase === 'notification_license_ok') ? $data : '';

        return response()->json([])
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $notificationText)
            ->header('notification_server_signature', $signature)
            ->header('notification_data', json_encode($responseData));
    }

    /**
     * Generate server signature for callback verification.
     * Same algorithm as original: SHA256(server_ips + product_id + license_code + email + root_url + date).
     */
    protected function generateServerSignature(?int $product_id, ?string $root_url, ?string $client_email, ?string $license_code): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel($this->getRawDomain($rootUrl));

        if (! is_array($rootIps)) {
            $rootIps = [];
        }

        sort($rootIps);

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
        if (in_array($url, [null, '', '0'], strict: true)) {
            return '';
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (empty($scheme)) {
            $url = 'http://'.$url;
        }

        return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST) ?? '');
    }

    /**
     * Get installation domain from URL.
     * Matches original getRootUrl($url, 1, 1, 0, 1): strips scheme, www, trailing slash, keeps path.
     * e.g. "https://www.example.com/helpdesk/" → "example.com/helpdesk".
     */
    protected function getInstallationDomain(?string $url): string
    {
        if (in_array($url, [null, '', '0'], strict: true) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->getRawDomain($url);
        }

        $parsed = parse_url($url);
        $host = str_ireplace('www.', '', $parsed['host'] ?? '');
        $path = rtrim($parsed['path'] ?? '', '/');

        return $host.$path;
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
     * Create license callback log (with duplicate prevention for same-day callbacks).
     */
    protected function createCallback(int $productId, ?int $userId, string $licenseCode, string $ip, string $domain, int $status): void
    {
        $today = now()->format('Y-m-d');

        // Prevent duplicate callbacks for the same license/IP/domain on the same day
        $exists = LicenseCallback::where('product_id', $productId)
            ->where('license_code', $licenseCode)
            ->where('callback_ip', $ip)
            ->where('callback_domain', $domain)
            ->whereDate('callback_date_time', $today)
            ->exists();

        if ($exists) {
            return;
        }

        LicenseCallback::create([
            'product_id' => $productId,
            'user_id' => $userId,
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
    protected function createReport(?int $productId, ?int $userId, ?string $licenseCode, string $text, int $system): void
    {
        LicenseReport::create([
            'product_id' => $productId ?: null,
            'user_id' => $userId ?: null,
            'license_code' => $licenseCode,
            'report_date_time' => now(),
            'report_text' => $text,
            'report_system' => $system,
            'report_status' => 1,
        ]);
    }
}
