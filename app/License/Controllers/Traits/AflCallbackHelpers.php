<?php

namespace App\License\Controllers\Traits;

use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseNotification;
use App\License\Models\LicenseReport;
use App\Model\Product\Product;
use Illuminate\Http\JsonResponse;

trait AflCallbackHelpers
{
    /**
     * Build notification response with headers.
     * Original format: empty JSON body + notification_* headers.
     * Matches returnServerNotification() from original:
     * - Fetches notification text from DB and replaces placeholders
     * - Only sends notification_data when notification_case is 'notification_license_ok'.
     *
     * $product/$license are passed in whenever the caller has already resolved them,
     * so their shortcodes (%PRODUCT_TITLE%, %LICENSE_LIMIT%, ...) can be substituted -
     * same as the original, which always had the full row in scope by the time it notified.
     *
     * @param  array<mixed>  $data
     */
    protected function notificationResponse(
        string $notificationCase,
        array $data = [],
        ?Product $product = null,
        ?License $license = null,
    ): JsonResponse {
        if (! in_array($notificationCase, ['notification_license_ok', 'notification_host_banned'], true)) {
            $this->bannedHostService->recordFailedLicensing((string) request()->ip());
        }

        $notification = LicenseNotification::first();
        $notificationText = $notification ? ($notification->{$notificationCase} ?? $notificationCase) : $notificationCase;
        $notificationText = $this->replaceNotificationPlaceholders((string) $notificationText, $product, $license);

        $root_url = (string) request()->input('root_url');
        $client_email = (string) request()->input('client_email');
        $license_code = (string) request()->input('license_code');
        $product_id = request()->input('product_id');

        $signature = $this->generateServerSignature($product_id, $root_url, $client_email, $license_code);

        // Original only returns notification_data when everything is OK
        $responseData = ($notificationCase === 'notification_license_ok') ? $data : '';

        return response()->json([])
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $notificationText)
            ->header('notification_server_signature', $signature)
            ->header('notification_data', (string) json_encode($responseData));
    }

    /**
     * Replace every %SHORTCODE% in notification text (all 17 from the original
     * returnServerNotification() bad_text_array/good_text_array pair).
     */
    protected function replaceNotificationPlaceholders(string $text, ?Product $product, ?License $license): string
    {
        $request = request();

        $placeholders = [
            '%ROOT_URL%', '%IP_ADDRESS%', '%CLIENT_EMAIL%', '%CLIENT_FNAME%', '%CLIENT_LNAME%',
            '%LICENSE_CODE%', '%PRODUCT_ID%', '%PRODUCT_TITLE%', '%PRODUCT_DESCRIPTION%',
            '%PRODUCT_URL_HOMEPAGE%', '%PRODUCT_URL_DOWNLOAD%', '%PRODUCT_VERSION%',
            '%LICENSE_EXPIRE_DATE%', '%LICENSE_CANCEL_DATE%', '%LICENSE_UPDATES_DATE%',
            '%LICENSE_SUPPORT_DATE%', '%LICENSE_LIMIT%',
        ];
        $replacements = [
            (string) $request->input('root_url', ''),
            (string) $request->ip(),
            (string) $request->input('client_email', ''),
            (string) $request->input('client_fname', ''),
            (string) $request->input('client_lname', ''),
            (string) $request->input('license_code', ''),
            (string) $request->input('product_id', ''),
            (string) ($product->name ?? ''),
            (string) ($product->description ?? ''),
            (string) ($product->product_url_homepage ?? ''),
            (string) ($product->product_url_download ?? ''),
            (string) ($product->version ?? ''),
            (string) ($license->license_expire_date ?? ''),
            (string) ($license->license_cancel_date ?? ''),
            (string) ($license->license_updates_date ?? ''),
            (string) ($license->license_support_date ?? ''),
            (string) ($license->license_limit ?? ''),
        ];

        return str_ireplace($placeholders, $replacements, $text);
    }

    /**
     * Generate server signature for callback verification.
     * Same algorithm as original: SHA256(server_ips + product_id + license_code + email + root_url + date).
     */
    protected function generateServerSignature(mixed $product_id, ?string $root_url, ?string $client_email, ?string $license_code): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel($this->getRawDomain($rootUrl));

        if (empty($rootIps)) {
            // Matches original: DNS resolution failed, no signature can be trusted.
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
        $today = now()->startOfDay();

        // Prevent duplicate callbacks for the same license/IP/domain on the same day
        $exists = LicenseCallback::where('product_id', $productId)
            ->where('license_code', $licenseCode)
            ->where('callback_ip', $ip)
            ->where('callback_domain', $domain)
            ->whereBetween('callback_date_time', [$today, $today->copy()->endOfDay()])
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
