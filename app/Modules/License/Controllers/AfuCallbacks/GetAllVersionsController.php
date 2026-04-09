<?php

namespace App\Modules\License\Controllers\AfuCallbacks;

use App\Model\Product\Product;
use App\Modules\License\Controllers\Traits\AfuCallbackHelpers;
use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\ProductVersion;
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

        $versions = ProductVersion::where('product_id', $product->id)
            ->where('version_status', 1)
            ->orderBy('version_date', 'desc')
            ->get();

        $responseData = array_merge(
            $product->only(['id', 'name', 'product_sku', 'product_key', 'status']),
            [
                'product_id' => $product->id,
                'product_title' => $product->name,
                'product_versions' => $versions->toArray(),
            ]
        );

        return $this->notificationResponse('notification_operation_ok', $responseData);
    }
}
