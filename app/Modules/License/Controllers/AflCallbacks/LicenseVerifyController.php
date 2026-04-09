<?php

namespace App\Modules\License\Controllers\AflCallbacks;

use App\Modules\License\Controllers\Traits\AflCallbackHelpers;
use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Models\Installation;
use App\Modules\License\Models\License;
use App\Modules\License\Services\InstallationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LicenseVerifyController extends Controller
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
     * Verify license for deployed product
     * POST /apl_callbacks/license_verify.php  OR  POST /api/licenseVerify
     */
    public function licenseVerify(Request $request)
    {
        $product_id        = $request->input('product_id');
        $root_url          = $request->input('root_url');
        $client_email      = $request->input('client_email');
        $license_code      = $request->input('license_code');
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
            $this->createReport($product_id, null, $license_code, 'License not found during verification', 1);
            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (!$validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);
            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), $validation['data'] ?? []);
        }

        $license = $validation['license'];

        // Verify installation exists
        $installation = Installation::where('license_code', $license_code)
            ->where('installation_status', 1)
            ->first();

        if (!$installation) {
            return $this->notificationResponse('notification_installation_not_found', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        // Update installation log
        $this->installationService->updateLogs([
            'license_code'    => $license_code,
            'root_url'        => $root_url,
            'version_number'  => $request->input('version_number'),
            'installation_ip' => $ip,
        ]);

        // Build response data
        $notificationData = [
            'license_code'        => $license->license_code,
            'product_id'          => $license->product_id,
            'license_status'      => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain'      => $license->license_domain,
            'license_ip'          => $license->license_ip,
        ];

        return $this->notificationResponse('notification_license_ok', $notificationData, $product_id, $client_email, $license_code, $root_url);
    }
}
