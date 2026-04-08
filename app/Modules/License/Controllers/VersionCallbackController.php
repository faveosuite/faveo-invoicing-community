<?php

namespace App\Modules\License\Controllers;

use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\ProductVersion;
use App\Modules\License\Models\VersionCallback;
use App\Modules\License\Models\VersionInstallation;
use App\Modules\License\Models\VersionNotification;
use App\Modules\License\Models\LicenseReport;
use App\Modules\License\Services\VersionService;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Version callback controller for auto-update checks.
 * Handles AFU (Auto File Update) callbacks from deployed client software.
 *
 * RESPONSE FORMAT: All callbacks return HTTP 200 with an empty JSON body
 * and notification data in response HEADERS (same as LicenseCallbackController):
 *   - notification_case: the notification key
 *   - notification_text: human-readable message from version_notifications table
 *   - notification_server_signature: SHA256 hash
 *   - notification_data: JSON-encoded data payload
 */
class VersionCallbackController extends Controller
{
    protected VersionService $versionService;
    protected LicenseValidator $validator;

    public function __construct(VersionService $versionService, LicenseValidator $validator)
    {
        $this->versionService = $versionService;
        $this->validator = $validator;
    }

    /**
     * Get latest version for product
     * POST /aus_callbacks/download_file.php (type=getVersions) OR POST /api/getVersions
     */
    public function getVersions(Request $request)
    {
        $product_id     = $request->input('product_id');
        $product_key    = $request->input('product_key');
        $current_version = $request->input('version_number');
        $ip = $request->ip();

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product
        $product = Product::where('id', $product_id)
            ->orWhere('product_key', $product_key)
            ->first();

        if (!$product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        if ($product->status != 1) {
            return $this->notificationResponse('notification_product_inactive', []);
        }

        // Get latest version
        $latestVersion = ProductVersion::where('product_id', $product->id)
            ->where('version_status', 1)
            ->orderBy('version_date', 'desc')
            ->first();

        if (!$latestVersion) {
            return $this->notificationResponse('notification_product_no_versions', []);
        }

        // Log callback
        $this->logCallback($product->id, $latestVersion->id, 'get_version', $ip, $request->input('root_url', ''));

        // Build response data — merge product + version, excluding sensitive fields
        $responseData = array_merge(
            $product->only(['id', 'name', 'product_sku', 'product_key', 'product_url_homepage', 'status']),
            [
                'product_id'           => $product->id,
                'product_title'        => $product->name,
                'version_id'           => $latestVersion->id,
                'version_number'       => $latestVersion->version_number,
                'version_date'         => $latestVersion->version_date,
                'version_changelog'    => $latestVersion->version_changelog,
                'version_install_file' => $latestVersion->version_install_file,
                'version_upgrade_file' => $latestVersion->version_upgrade_file,
                'version_status'       => $latestVersion->version_status,
            ]
        );

        return $this->notificationResponse('notification_operation_ok', $responseData);
    }

    /**
     * Get all versions for product
     * POST /api/getAllVersions
     */
    public function getAllVersions(Request $request)
    {
        $product_id  = $request->input('product_id');
        $product_key = $request->input('product_key');
        $ip = $request->ip();

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product
        $product = Product::where('id', $product_id)
            ->orWhere('product_key', $product_key)
            ->first();

        if (!$product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        $versions = ProductVersion::where('product_id', $product->id)
            ->where('version_status', 1)
            ->orderBy('version_date', 'desc')
            ->get();

        $responseData = array_merge(
            $product->only(['id', 'name', 'product_sku', 'product_key', 'status']),
            [
                'product_id'       => $product->id,
                'product_title'    => $product->name,
                'product_versions' => $versions->toArray(),
            ]
        );

        return $this->notificationResponse('notification_operation_ok', $responseData);
    }

    /**
     * Fetch version query/SQL
     * POST /api/fetchQuery
     */
    public function fetchQuery(Request $request)
    {
        $product_id     = $request->input('product_id');
        $product_key    = $request->input('product_key');
        $version_number = $request->input('version_number');
        $query_type     = $request->input('query_type', 'install'); // install or upgrade
        $ip = $request->ip();

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product
        $product = Product::where('id', $product_id)
            ->orWhere('product_key', $product_key)
            ->first();

        if (!$product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find version
        $version = ProductVersion::where('product_id', $product->id)
            ->where('version_number', $version_number)
            ->first();

        if (!$version) {
            return $this->notificationResponse('notification_version_not_found', []);
        }

        if ($version->version_status != 1) {
            return $this->notificationResponse('notification_version_inactive', []);
        }

        // Get the appropriate query
        $rawQuery = ($query_type === 'upgrade')
            ? $version->version_raw_upgrade_query
            : $version->version_raw_install_query;

        // Log callback
        $this->logCallback($product->id, $version->id, 'fetch_query', $ip, $request->input('root_url', ''));

        $responseData = [
            'product_id'     => $product->id,
            'version_id'     => $version->id,
            'version_number' => $version->version_number,
            'query_type'     => $query_type,
        ];

        // Return raw query in body, notification data in headers
        return response(json_encode($rawQuery))
            ->header('Content-Type', 'application/json')
            ->header('notification_case', 'notification_operation_ok')
            ->header('notification_text', $this->getNotificationText('notification_operation_ok'))
            ->header('notification_server_signature', $this->generateSignature($product->id))
            ->header('notification_data', json_encode($responseData));
    }

    /**
     * Download version file
     * POST /aus_callbacks/download_file.php  OR  POST /api/downloadFile
     */
    public function downloadFile(Request $request)
    {
        $product_id     = $request->input('product_id');
        $product_key    = $request->input('product_key');
        $version_number = $request->input('version_number');
        $file_type      = $request->input('file_type', 'version_install_file');
        $ip = $request->ip();

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product
        $product = Product::where('id', $product_id)
            ->orWhere('product_key', $product_key)
            ->first();

        if (!$product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find version
        $version = ProductVersion::where('product_id', $product->id)
            ->where('version_number', $version_number)
            ->where('version_status', 1)
            ->first();

        if (!$version) {
            return $this->notificationResponse('notification_version_not_found', []);
        }

        // Get file path
        $allowedTypes = [
            'version_install_file', 'version_install_query',
            'version_upgrade_file', 'version_upgrade_query',
        ];

        if (!in_array($file_type, $allowedTypes)) {
            return $this->notificationResponse('notification_invalid_parameter', []);
        }

        $filePath = $version->{$file_type};
        if (!$filePath) {
            $notifKey = str_contains($file_type, 'install')
                ? 'notification_install_archive_not_found'
                : 'notification_upgrade_archive_not_found';
            return $this->notificationResponse($notifKey, []);
        }

        // Check limits
        if (str_contains($file_type, 'install')) {
            if ($version->version_install_limit && $version->version_install_count >= $version->version_install_limit) {
                return $this->notificationResponse('notification_install_limit_reached', []);
            }
            $version->increment('version_install_count');
        } else {
            if ($version->version_upgrade_limit && $version->version_upgrade_count >= $version->version_upgrade_limit) {
                return $this->notificationResponse('notification_upgrade_limit_reached', []);
            }
            $version->increment('version_upgrade_count');
        }

        // Log callback
        $this->logCallback($product->id, $version->id, 'download', $ip, $request->input('root_url', ''));

        $responseData = [
            'product_id'     => $product->id,
            'version_id'     => $version->id,
            'version_number' => $version->version_number,
            'file_type'      => $file_type,
        ];

        // If file exists on disk, download it
        $fullPath = storage_path('app/' . $filePath);
        if (file_exists($fullPath)) {
            return response()->download($fullPath)
                ->header('notification_case', 'notification_operation_ok')
                ->header('notification_server_signature', $this->generateSignature($product->id))
                ->header('notification_data', json_encode($responseData));
        }

        // Return file path in headers for external download
        return $this->notificationResponse('notification_operation_ok', array_merge($responseData, [
            'file_path' => $filePath,
        ]));
    }

    // =========================================================================
    // RESPONSE HELPERS
    // =========================================================================

    /**
     * Build notification response with headers (same format as license callbacks)
     */
    protected function notificationResponse(string $notificationCase, array $data = [])
    {
        return response()->json([])
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $this->getNotificationText($notificationCase))
            ->header('notification_server_signature', $this->generateSignature())
            ->header('notification_data', json_encode($data));
    }

    /**
     * Get notification text from version_notifications table
     */
    protected function getNotificationText(string $case): string
    {
        $notification = VersionNotification::first();
        return $notification ? ($notification->{$case} ?? $case) : $case;
    }

    /**
     * Generate signature for version callbacks
     */
    protected function generateSignature(?int $productId = null): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel(str_ireplace('www.', '', parse_url($rootUrl, PHP_URL_HOST) ?? ''));

        if (empty($rootIps)) {
            return '';
        }

        return hash('sha256', implode('', $rootIps) . $productId . gmdate('Y-m-d'));
    }

    /**
     * Log version callback
     */
    protected function logCallback(int $productId, int $versionId, string $type, string $ip, string $path): void
    {
        VersionCallback::create([
            'product_id'      => $productId,
            'version_id'      => $versionId,
            'callback_type'   => $type,
            'callback_ip'     => $ip,
            'callback_path'   => $path,
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);
    }
}
