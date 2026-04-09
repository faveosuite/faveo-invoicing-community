<?php

namespace App\Modules\License\Controllers\AfuCallbacks;

use App\Modules\License\Controllers\Traits\AfuCallbackHelpers;
use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\ProductVersion;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GetVersionsController extends Controller
{
    use AfuCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
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
}
