<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\Model\Product\ProductUpload;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GetAllVersionsController extends Controller
{
    use AfuCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get all versions for product
     * POST /api/getAllVersions.
     */
    public function getAllVersions(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $user_local_path = $request->input('user_local_path');
        $script_signature = $request->input('script_signature');
        $ip = $this->validator->resolveIp($request);

        // Validate basic request (IP, product_id integer, product_key, user_local_path, script_signature non-empty)
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

        // Get all versions
        $versions = ProductUpload::where('product_id', $product->id)
            ->orderBy('id', 'desc')
            ->get();

        if ($versions->isEmpty()) {
            return $this->notificationResponse('notification_product_no_versions', []);
        }

        // Build response data (merge product + version, filter sensitive fields)
        $productData = $product->toArray();
        $versionData = $versions->first()->toArray();
        $versionData['product_versions'] = $versions->toArray();

        $responseData = $this->filterSensitiveData(
            array_merge($productData, $versionData),
            ['version_install_limit', 'version_upgrade_limit', 'version_changelog']
        );

        // Log callback (1 = version check)
        $this->logCallback($product->id, $versions->first()->id, 1, $ip, $user_local_path);

        return $this->notificationResponse('notification_operation_ok', $responseData);
    }
}
