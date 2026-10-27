<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\License;
use App\License\Services\LicenseService;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LicensedPluginsController extends Controller
{
    use AfuCallbackHelpers;

    public function __construct(protected LicenseValidator $validator, protected LicenseService $licenseService)
    {
    }

    /**
     * Add-ons the client's licence can install (replaces the unauthenticated /api/pluginInfo).
     * POST /api/licensedPlugins — signed like /api/getAllVersions.
     */
    public function licensedPlugins(Request $request): mixed
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $user_local_path = $request->input('user_local_path');
        $script_signature = $request->input('script_signature');
        $license_code = (string) $request->input('license_code', '');
        $ip = $this->validator->resolveIp($request);

        if (! $this->validator->isValidAfuRequest($ip, $product_id, $product_key, $user_local_path, $script_signature)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        $product = Product::where('id', $product_id)
            ->where('product_key', $product_key)
            ->first();

        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        if (! $this->validator->verifyAfuScriptSignature($script_signature, $product_id, $product_key)) {
            return $this->notificationResponse('notification_invalid_signature', [], $product);
        }

        // Only an active licence of this very product unlocks its customer's add-ons.
        $license = $license_code === '' ? null : License::where('license_code', $license_code)
            ->where('product_id', $product->id)
            ->where('license_status', 1)
            ->first();

        if (! $license) {
            return $this->notificationResponse('notification_license_not_found', [], $product);
        }

        return $this->notificationResponse('notification_operation_ok', [
            'plugins' => $this->licenseService->getLicensedPlugins($license),
        ], $product);
    }
}
