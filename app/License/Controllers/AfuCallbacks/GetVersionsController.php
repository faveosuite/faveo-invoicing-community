<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\ProductVersion;
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
     * POST /aus_callbacks/download_file.php (type=getVersions) OR POST /api/getVersions.
     */
    public function getVersions(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $version_number = $request->input('version_number');
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

        // Verify script signature
        if (! $this->validator->verifyAfuScriptSignature($script_signature, $product_id, $product_key)) {
            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Get specified version or latest active one
        if (! empty($version_number)) {
            $version = ProductVersion::where('product_id', $product->id)
                ->where('version_number', $version_number)
                ->first();
        } else {
            $version = ProductVersion::where('product_id', $product->id)
                ->where('version_status', 1)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (! $version) {
            $notifKey = ! empty($version_number)
                ? 'notification_version_not_found'
                : 'notification_product_no_versions';

            return $this->notificationResponse($notifKey, []);
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

        // Build response data (merge product + version, filter sensitive fields)
        $responseData = $this->filterSensitiveData(
            array_merge($product->toArray(), $version->toArray())
        );

        // Log callback (1 = version check)
        $this->logCallback($product->id, $version->id, 1, $ip, $user_local_path);

        return $this->notificationResponse('notification_operation_ok', $responseData);
    }
}
