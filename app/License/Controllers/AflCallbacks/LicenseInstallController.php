<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Services\InstallationService;
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
     * POST /apl_callbacks/license_install.php  OR  POST /api/licenseInstall.
     */
    public function licenseInstall(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $license_signature = $request->input('license_signature');
        $version_number = $request->input('version_number');
        $client_id = $request->input('client_id');
        $ip = $this->validator->resolveIp($request);

        // Validate basic request (IP, product_id, root_url, license_code/email)
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url, $license_code, $client_email)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Validate installation hash: hash('sha256', root_url + email + code)
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
            $this->createReport($product_id ?? 0, null, $license_code, 'Host banned: '.$ip, 1);

            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product exists
        $product = $this->validator->validateProduct($product_id);
        if (! $product) {
            $this->createReport(0, null, $license_code, 'Product not found (ID: '.$product_id.')', 1);

            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find license (with LicensePlugin multi-product support)
        $license = $this->validator->findLicense($license_code, $client_email, $product_id);

        if (! $license) {
            $this->createReport($product_id, null, $license_code, 'License not found', 1);

            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain restrictions)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license->license_code, $validation['error'], 1);

            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), $validation['data'] ?? []);
        }

        $license = $validation['license'];
        $installation_domain = $this->getInstallationDomain($root_url);

        // Check if installation already exists on this domain by another user
        $existingInstallation = Installation::where('product_id', $product_id)
            ->where('installation_ip', $ip)
            ->where('installation_domain', $installation_domain)
            ->first();

        if ($existingInstallation) {
            if ($license->license_code != $existingInstallation->license_code
                || ($this->validator->validateIntegerValue($client_id) && $client_id != $existingInstallation->user_id)) {
                $this->createReport($product_id, $license->user_id, $license->license_code, "Installation on $installation_domain ($ip) belongs to another user", 1);

                return $this->notificationResponse('notification_domain_in_use', []);
            }
        }

        // Check installation limit
        if ($license->license_limit > 0) {
            // Count other installations (different IP or domain)
            $otherInstallations = Installation::where('product_id', $product_id)
                ->where('license_code', $license->license_code)
                ->where(function ($query) use ($ip, $installation_domain) {
                    $query->where('installation_ip', '!=', $ip)
                        ->orWhere('installation_domain', '!=', $installation_domain);
                })
                ->count();

            if ($otherInstallations >= $license->license_limit) {
                $this->createReport($product_id, $license->user_id, $license->license_code, "Maximum installations limit ({$license->license_limit}) reached", 1);

                return $this->notificationResponse('notification_license_limit', []);
            }

            // Total installations check (catches cases where limit was reduced after installations)
            $allInstallations = Installation::where('product_id', $product_id)
                ->where(function ($query) use ($client_id, $license) {
                    $query->where('user_id', $client_id)
                        ->whereNotNull('user_id')
                        ->orWhere('license_code', $license->license_code);
                })
                ->count();

            if ($allInstallations > $license->license_limit) {
                $this->createReport($product_id, $license->user_id, $license->license_code, "Maximum installations limit ({$license->license_limit}) exceeded", 1);

                return $this->notificationResponse('notification_license_limit', []);
            }
        }

        // Register/update installation
        $this->installationService->register([
            'product_id' => $product_id,
            'user_id' => $license->user_id ?: null,
            'license_code' => $license->license_code,
            'installation_ip' => $ip,
            'installation_domain' => $installation_domain,
            'installation_hash' => $installation_hash,
            'installation_status' => 1,
        ]);

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license->license_code, $ip, $root_url, 1);

        // Update installation logs
        $this->installationService->updateLogs([
            'license_code' => $license->license_code,
            'root_url' => $root_url,
            'version_number' => $version_number,
            'installation_ip' => $ip,
        ]);

        return $this->notificationResponse('notification_license_ok', [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'license_status' => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain' => $license->license_domain,
            'license_ip' => $license->license_ip,
        ], $product_id, $client_email, $license->license_code, $root_url);
    }
}
