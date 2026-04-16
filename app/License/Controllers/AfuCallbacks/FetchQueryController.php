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
        $query_type = $request->input('query_type');
        $user_local_path = $request->input('user_local_path');
        $script_signature = $request->input('script_signature');
        $ip = $request->ip();

        // Supported query types
        $supportedQueryTypes = ['install', 'upgrade'];

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

        // Validate query_type
        if (empty($query_type) || ! in_array($query_type, $supportedQueryTypes)) {
            return $this->notificationResponse('notification_invalid_parameter', []);
        }

        // Get the appropriate raw query and check it exists
        if ($query_type === 'install') {
            $rawQuery = $version->version_raw_install_query;
            if (empty($rawQuery)) {
                return $this->notificationResponse('notification_raw_install_query_not_found', []);
            }
            $callback_type = 2; // installation
        } else {
            $rawQuery = $version->version_raw_upgrade_query;
            if (empty($rawQuery)) {
                return $this->notificationResponse('notification_raw_upgrade_query_not_found', []);
            }
            $callback_type = 3; // upgrade
        }

        // Log callback
        $this->logCallback($product->id, $version->id, $callback_type, $ip, $user_local_path);

        $responseData = $this->filterSensitiveData(
            array_merge($product->toArray(), $version->toArray())
        );

        // Return raw query in body, notification data in headers
        return response(json_encode($rawQuery))
            ->header('Content-Type', 'application/json')
            ->header('notification_case', 'notification_operation_ok')
            ->header('notification_text', $this->getNotificationText('notification_operation_ok'))
            ->header('notification_server_signature', $this->generateSignature($product->id, $product_key))
            ->header('notification_data', json_encode($responseData));
    }
}
