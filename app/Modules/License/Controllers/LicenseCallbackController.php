<?php

namespace App\Modules\License\Controllers;

use App\Model\Product\Product;
use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\Installation;
use App\Modules\License\Models\License;
use App\Modules\License\Models\LicenseCallback;
use App\Modules\License\Models\LicenseNotification;
use App\Modules\License\Models\LicenseReport;
use App\Modules\License\Models\LicenseScheme;
use App\Modules\License\Services\InstallationService;
use App\Modules\License\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * License callback controller for external license verification.
 * Handles APL (Active Product License) callbacks from deployed client software.
 *
 * RESPONSE FORMAT: All callbacks return HTTP 200 with an empty JSON body
 * and notification data in response HEADERS:
 *   - notification_case: the notification key (e.g. notification_license_ok)
 *   - notification_text: the human-readable message from license_notifications table
 *   - notification_server_signature: SHA256 hash for verification
 *   - notification_data: JSON-encoded data payload
 */
class LicenseCallbackController extends Controller
{
    protected LicenseValidator $validator;
    protected LicenseService $licenseService;
    protected InstallationService $installationService;

    public function __construct(
        LicenseValidator $validator,
        LicenseService $licenseService,
        InstallationService $installationService
    ) {
        $this->validator = $validator;
        $this->licenseService = $licenseService;
        $this->installationService = $installationService;
    }

    /**
     * Test connection between Faveo and License Manager
     * POST /apl_callbacks/connection_test.php  OR  POST /api/ConnectionTest.
     */
    public function connection(Request $request)
    {
        $product_id = $request->input('product_id');
        $connection_hash = $request->input('connection_hash');
        $root_url = $request->input('root_url');
        $ip = $request->ip();

        if (! $this->validator->isValidConnection($product_id, $connection_hash)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        return $this->notificationResponse('notification_license_ok', [
            'connection' => 'successful',
        ], $product_id, '', '', $root_url);
    }

    /**
     * Register license installation
     * POST /apl_callbacks/license_install.php  OR  POST /api/licenseInstall.
     */
    public function licenseInstall(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $ip = $request->ip();

        // Validate basic request
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Check banned hosts
        if ($this->validator->isBanned($ip)) {
            $this->createReport($product_id, null, $license_code, 'Host banned: '.$ip, 1);

            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product
        $product = $this->validator->validateProduct($product_id);
        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        if ($product->status != 1) {
            return $this->notificationResponse('notification_product_inactive', []);
        }

        // Find license
        $license = License::where('license_code', $license_code)->first();
        if (! $license) {
            $license = $this->validator->findLicenseByEmail($client_email, $product_id);
        }

        if (! $license) {
            $this->createReport($product_id, null, $license_code, 'License not found', 1);

            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);

            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), []);
        }

        $license = $validation['license'];

        // Check installation limit
        $activeCount = $this->installationService->countActiveInstallations($license_code);
        if ($license->license_limit > 0 && $activeCount >= $license->license_limit) {
            $this->createReport($product_id, $license->user_id, $license_code, 'Installation limit reached', 1);

            return $this->notificationResponse('notification_license_limit', []);
        }

        // Register/update installation
        $this->installationService->register([
            'product_id' => $product_id,
            'user_id' => $license->user_id ?? 0,
            'license_code' => $license_code,
            'installation_ip' => $ip,
            'installation_domain' => $this->getRawDomain($root_url),
            'installation_hash' => $installation_hash,
            'installation_status' => 1,
        ]);

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        $signature = $this->generateServerSignature($product_id, $root_url, $client_email, $license_code);

        return $this->notificationResponse('notification_license_ok', [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'license_status' => $license->license_status,
        ], $product_id, $client_email, $license_code, $root_url);
    }

    /**
     * Get license scheme query
     * POST /apl_callbacks/license_scheme.php  OR  POST /api/licenseScheme.
     */
    public function licenseScheme(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $ip = $request->ip();

        // Validate basic request
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Verify license
        $license = License::where('license_code', $license_code)->first();
        if (! $license) {
            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Get scheme
        $isPlugin = $request->input('isPlugin', false);
        $tableCreate = $request->input('tableCreate', false);

        $scheme = LicenseScheme::where('scheme_status', 1)->first();
        if (! $scheme) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        return $this->notificationResponse('notification_license_ok', [
            'scheme_id' => $scheme->id,
            'scheme_name' => 'license_scheme',
            'scheme_sql' => $scheme->scheme_query,
        ], $product_id, $client_email, $license_code, $root_url);
    }

    /**
     * Verify license for deployed product
     * POST /apl_callbacks/license_verify.php  OR  POST /api/licenseVerify.
     */
    public function licenseVerify(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $ip = $request->ip();

        // Validate basic request
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Check banned hosts
        if ($this->validator->isBanned($ip)) {
            $this->createReport($product_id, null, $license_code, 'Host banned: '.$ip, 1);

            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product
        $product = $this->validator->validateProduct($product_id);
        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        if ($product->status != 1) {
            return $this->notificationResponse('notification_product_inactive', []);
        }

        // Find license
        $license = License::where('license_code', $license_code)->first();
        if (! $license) {
            $license = $this->validator->findLicenseByEmail($client_email, $product_id);
        }

        if (! $license) {
            $this->createReport($product_id, null, $license_code, 'License not found during verification', 1);

            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);

            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), $validation['data'] ?? []);
        }

        $license = $validation['license'];

        // Verify installation exists
        $installation = Installation::where('license_code', $license_code)
            ->where('installation_status', 1)
            ->first();

        if (! $installation) {
            return $this->notificationResponse('notification_installation_not_found', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        // Update installation log
        $this->installationService->updateLogs([
            'license_code' => $license_code,
            'root_url' => $root_url,
            'version_number' => $request->input('version_number'),
            'installation_ip' => $ip,
        ]);

        // Build response data
        $notificationData = [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'license_status' => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain' => $license->license_domain,
            'license_ip' => $license->license_ip,
        ];

        return $this->notificationResponse('notification_license_ok', $notificationData, $product_id, $client_email, $license_code, $root_url);
    }

    // =========================================================================
    // RESPONSE HELPERS — match original license app response format exactly
    // =========================================================================

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
