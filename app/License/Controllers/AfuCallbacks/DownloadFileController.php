<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\ProductVersion;
use App\Model\Product\Product;
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
        $ip = $request->ip();

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product
        $product = Product::where('id', $product_id)
            ->orWhere('product_key', $product_key)
            ->first();

        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find version
        $version = ProductVersion::where('product_id', $product->id)
            ->where('version_number', $version_number)
            ->where('version_status', 1)
            ->first();

        if (! $version) {
            return $this->notificationResponse('notification_version_not_found', []);
        }

        // Get file path
        $allowedTypes = [
            'version_install_file', 'version_install_query',
            'version_upgrade_file', 'version_upgrade_query',
        ];

        if (! in_array($file_type, $allowedTypes)) {
            return $this->notificationResponse('notification_invalid_parameter', []);
        }

        $filePath = $version->{$file_type};
        if (! $filePath) {
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
            'product_id' => $product->id,
            'version_id' => $version->id,
            'version_number' => $version->version_number,
            'file_type' => $file_type,
        ];

        // If file exists on disk, download it
        $fullPath = storage_path('app/'.$filePath);
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
}
