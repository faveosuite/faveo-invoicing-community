<?php

namespace App\Modules\License\Controllers\AflCallbacks;

use App\Modules\License\Controllers\Traits\AflCallbackHelpers;
use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\License;
use App\Modules\License\Services\InstallationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LicenseInstallController extends Controller
{
    use AflCallbackHelpers;

    protected LicenseValidator $validator;
    protected InstallationService $installationService;

    public function __construct(LicenseValidator $validator, InstallationService $installationService)
    {
        $this->validator = $validator;
        $this->installationService = $installationService;
    }

    /**
     * Register license installation
     * POST /apl_callbacks/license_install.php  OR  POST /api/licenseInstall
     */
    public function licenseInstall(Request $request)
    {
        $product_id    = $request->input('product_id');
        $root_url      = $request->input('root_url');
        $client_email  = $request->input('client_email');
        $license_code  = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $ip = $request->ip();

        // Validate basic request
        if (!$this->validator->isValidLicenseRequest($ip, $product_id, $root_url)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Check banned hosts
        if ($this->validator->isBanned($ip)) {
            $this->createReport($product_id, null, $license_code, 'Host banned: ' . $ip, 1);
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product
        $product = $this->validator->validateProduct($product_id);
        if (!$product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        if ($product->status != 1) {
            return $this->notificationResponse('notification_product_inactive', []);
        }

        // Find license
        $license = License::where('license_code', $license_code)->first();
        if (!$license) {
            $license = $this->validator->findLicenseByEmail($client_email, $product_id);
        }

        if (!$license) {
            $this->createReport($product_id, null, $license_code, 'License not found', 1);
            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (!$validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);
            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), []);
        }

        $license = $validation['license'];

        // Check installation limit
        $activeCount = $this->installationService->countActiveInstallations($license_code);
        if ($license->license_limit > 0 && $activeCount >= $license->license_limit) {
            $this->createReport($product_id, $license->user_id, $license_code, 'Installation limit reached', 1);
            return $this->notificationResponse('notification_license_limit', []);
        }

        // Register/update installation
        $this->installationService->register([
            'product_id'        => $product_id,
            'user_id'           => $license->user_id ?? 0,
            'license_code'      => $license_code,
            'installation_ip'   => $ip,
            'installation_domain' => $this->getRawDomain($root_url),
            'installation_hash' => $installation_hash,
            'installation_status' => 1,
        ]);

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        return $this->notificationResponse('notification_license_ok', [
            'license_code'  => $license->license_code,
            'product_id'    => $license->product_id,
            'license_status' => $license->license_status,
        ], $product_id, $client_email, $license_code, $root_url);
    }
}
