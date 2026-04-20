<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DownloadFileController extends Controller
{
    use AfuCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Download version file
     * POST /aus_callbacks/download_file.php  OR  POST /api/downloadFile.
     */
    public function downloadFile(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $version_number = $request->input('version_number');
        $file_type = $request->input('file_type', 'version_install_file');
        $user_local_path = $request->input('user_local_path');
        $script_signature = $request->input('script_signature');
        $ip = $this->validator->resolveIp($request);

        // Validate basic request
        if (! $this->validator->isValidAfuRequest($ip, $product_id, $product_key, $user_local_path, $script_signature)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product (original uses AND for both product_id and product_key)
        $product = Product::where('id', $product_id)
            ->where('product_key', $product_key)
            ->first();

        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Get specified version or latest active one
        if (! empty($version_number)) {
            $version = ProductUpload::where('product_id', $product->id)
                ->where('version', $version_number)
                ->first();
        } else {
            $version = ProductUpload::where('product_id', $product->id)
                ->active()
                ->orderBy('id', 'desc')
                ->first();
        }

        if (! $version) {
            $notifKey = ! empty($version_number)
                ? 'notification_version_not_found'
                : 'notification_product_no_versions';

            return $this->notificationResponse($notifKey, []);
        }

        // Verify script signature
        if (! $this->validator->verifyAfuScriptSignature($script_signature, $product_id, $product_key)) {
            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Check version status
        if ($version->version_status != 1) {
            return $this->notificationResponse('notification_version_inactive', []);
        }

        // Check version expiration
        if ($this->validator->verifyDateTime($version->version_expire_date, 'Y-m-d')
            && $version->version_expire_date < date('Y-m-d')) {
            return $this->notificationResponse('notification_version_expired', []);
        }

        // Validate file_type
        $allowedTypes = [
            'version_install_file', 'version_install_query',
            'version_upgrade_file', 'version_upgrade_query',
        ];

        if (empty($file_type) || ! in_array($file_type, $allowedTypes)) {
            return $this->notificationResponse('notification_invalid_parameter', []);
        }

        // Get file path and validate it exists
        $filePath = $version->{$file_type};
        if (empty($filePath)) {
            $notifKey = match (true) {
                $file_type === 'version_install_file' => 'notification_install_archive_not_found',
                $file_type === 'version_install_query' => 'notification_install_query_not_found',
                $file_type === 'version_upgrade_file' => 'notification_upgrade_archive_not_found',
                $file_type === 'version_upgrade_query' => 'notification_upgrade_query_not_found',
                default => 'notification_unknown_error',
            };

            return $this->notificationResponse($notifKey, []);
        }

        // Check install/upgrade limits
        if ($file_type === 'version_install_file') {
            if ($version->version_install_limit > 0 && $version->version_install_count >= $version->version_install_limit) {
                return $this->notificationResponse('notification_install_limit_reached', []);
            }
            $version->increment('version_install_count');
            $callback_type = 2; // installation
        } elseif ($file_type === 'version_upgrade_file') {
            if ($version->version_upgrade_limit > 0 && $version->version_upgrade_count >= $version->version_upgrade_limit) {
                return $this->notificationResponse('notification_upgrade_limit_reached', []);
            }
            $version->increment('version_upgrade_count');
            $callback_type = 3; // upgrade
        } else {
            // Query files: install_query = installation(2), upgrade_query = upgrade(3)
            $callback_type = str_contains($file_type, 'install') ? 2 : 3;
        }

        // Log callback
        $this->logCallback($product->id, $version->id, $callback_type, $ip, $user_local_path);

        $responseData = $this->filterSensitiveData(
            array_merge($product->toArray(), $version->toArray())
        );

        // If file exists on disk, download it
        $fullPath = storage_path('app/'.$filePath);
        if (file_exists($fullPath)) {
            return response()->download($fullPath)
                ->header('notification_case', 'notification_operation_ok')
                ->header('notification_server_signature', $this->generateSignature($product->id, $product_key))
                ->header('notification_data', json_encode($responseData));
        }

        // Return file path in headers for external download
        return $this->notificationResponse('notification_operation_ok', array_merge($responseData, [
            'file_path' => $filePath,
        ]));
    }
}
