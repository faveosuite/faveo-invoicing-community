<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\ProductVersion;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FetchQueryController extends Controller
{
    use AfuCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Fetch version query/SQL
     * POST /api/fetchQuery.
     */
    public function fetchQuery(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $version_number = $request->input('version_number');
        $query_type = $request->input('query_type', 'install'); // install or upgrade
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
            ->first();

        if (! $version) {
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
            'product_id' => $product->id,
            'version_id' => $version->id,
            'version_number' => $version->version_number,
            'query_type' => $query_type,
        ];

        // Return raw query in body, notification data in headers
        return response(json_encode($rawQuery))
            ->header('Content-Type', 'application/json')
            ->header('notification_case', 'notification_operation_ok')
            ->header('notification_text', $this->getNotificationText('notification_operation_ok'))
            ->header('notification_server_signature', $this->generateSignature($product->id))
            ->header('notification_data', json_encode($responseData));
    }
}
