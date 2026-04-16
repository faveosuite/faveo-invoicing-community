<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Services\InstallationService;
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
     * POST /apl_callbacks/license_verify.php  OR  POST /api/licenseVerify.
     */
    public function licenseVerify(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $license_signature = $request->input('license_signature');
        $client_id = $request->input('client_id');
        $is_cloud = $request->input('is_cloud');
        $ip = $request->ip();

        // Cloud IP override
        if ($is_cloud) {
            $ip = '138.197.237.160';
        }

        // Validate basic request (IP, product_id, root_url, license_code/email)
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url, $license_code, $client_email)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Validate installation hash
        if (! $this->validator->validateInstallationHash($installation_hash, $root_url, $client_email, $license_code)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Verify license signature
        if (! $this->validator->verifyScriptSignature($license_signature, $product_id, $root_url, $client_email, $license_code)) {
            $this->createReport($product_id ?? 0, null, $license_code, 'Invalid license signature', 1);

            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Check banned hosts
        if ($this->validator->isBanned($ip)) {
            $this->createReport($product_id, null, $license_code, 'Host banned: '.$ip, 1);

            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product exists
        $product = $this->validator->validateProduct($product_id);
        if (! $product) {
            $this->createReport(0, null, $license_code, 'Product not found (ID: '.$product_id.')', 1);

            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find license (with LicensePlugin multi-product support)
        $license = null;
        if (! empty($license_code)) {
            $license = $this->validator->findLicenseWithPlugins($license_code, $product_id);
        }
        if (! $license) {
            $license = $this->validator->findLicenseByEmail($client_email, $product_id);
        }

        if (! $license) {
            $this->createReport($product_id, null, $license_code, 'License not found during verification', 1);

            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);

            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), $validation['data'] ?? []);
        }

        $license = $validation['license'];
        $installation_domain = $this->getRawDomain($root_url);

        // Check installation ownership (same as original)
        $existingInstallation = Installation::where('product_id', $product_id)
            ->where('installation_ip', $ip)
            ->where('installation_domain', $installation_domain)
            ->first();

        if ($existingInstallation) {
            if ((! empty($license_code) && $license_code != $existingInstallation->license_code)
                || ($this->validator->validateIntegerValue($client_id) && $client_id != $existingInstallation->user_id)) {
                $this->createReport($product_id, $license->user_id, $license_code, "Installation on $installation_domain ($ip) belongs to another user", 1);

                return $this->notificationResponse('notification_domain_in_use', []);
            }
        }

        // Check installation limit
        if ($license->license_limit > 0) {
            $allInstallations = Installation::where('product_id', $product_id)
                ->where(function ($query) use ($client_id, $license_code) {
                    $query->where('user_id', $client_id)
                        ->whereNotNull('user_id')
                        ->orWhere('license_code', $license_code);
                })
                ->count();

            if ($allInstallations > $license->license_limit) {
                $this->createReport($product_id, $license->user_id, $license_code, "Maximum installations limit ({$license->license_limit}) exceeded", 1);

                return $this->notificationResponse('notification_license_limit', []);
            }
        }

        // Verify installation exists and is active (matching original: product + client/license + IP + domain + hash + status)
        $installation = Installation::where('product_id', $product_id)
            ->where(function ($query) use ($client_id, $license_code) {
                $query->where('user_id', $client_id)
                    ->orWhere('license_code', $license_code);
            })
            ->where(function ($query) use ($ip) {
                $query->where('installation_ip', $ip)
                    ->orWhere('installation_disable_ip_verification', 1);
            })
            ->where('installation_domain', $installation_domain)
            ->where('installation_hash', $installation_hash)
            ->where('installation_status', 1)
            ->first();

        if (! $installation) {
            $this->createReport($product_id, $license->user_id, $license_code, 'Installation does not exist or is inactive', 1);

            return $this->notificationResponse('notification_installation_not_found', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        // Update installation log
        $this->installationService->updateLogs([
            'license_code' => $license_code,
            'root_url' => $root_url,
            'version_number' => $request->input('version_number'),
            'installation_ip' => $ip,
        ]);

        // Build response data
        $notificationData = [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'license_status' => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain' => $license->license_domain,
            'license_ip' => $license->license_ip,
        ];

        return $this->notificationResponse('notification_license_ok', $notificationData, $product_id, $client_email, $license_code, $root_url);
    }
}
