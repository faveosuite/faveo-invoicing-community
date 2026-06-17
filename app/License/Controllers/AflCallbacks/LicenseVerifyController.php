<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Services\InstallationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LicenseVerifyController extends Controller
{
    use AflCallbackHelpers;

    public function __construct(protected LicenseValidator $validator, protected InstallationService $installationService)
    {
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
        $ip = $this->validator->resolveIp($request);

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
            $this->createReport($product_id ?? null, userId: null, licenseCode: $license_code, text: 'Invalid license signature', system: 1);

            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Check banned hosts
        if ($this->validator->isBanned($ip)) {
            $this->createReport($product_id ?? null, userId: null, licenseCode: $license_code, text: 'Host banned: '.$ip, system: 1);

            return $this->notificationResponse('notification_host_banned', []);
        }

        // Verify product exists
        $product = $this->validator->validateProduct($product_id);
        if (!$product instanceof \App\Model\Product\Product) {
            $this->createReport(productId: null, userId: null, licenseCode: $license_code, text: 'Product not found (ID: '.$product_id.')', system: 1);

            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find license (with LicensePlugin multi-product support)
        $license = $this->validator->findLicense($license_code, $client_email, $product_id);

        if (!$license instanceof \App\License\Models\License) {
            $this->createReport($product_id, userId: null, licenseCode: $license_code, text: 'License not found during verification', system: 1);

            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            $this->createReport($product_id, $license->user_id, $license_code, $validation['error'], 1);

            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), $validation['data'] ?? []);
        }

        $license = $validation['license'];
        $installation_domain = $this->getInstallationDomain($root_url);

        // Check installation ownership (same as original)
        $existingInstallation = Installation::where('product_id', $product_id)
            ->where('installation_ip', $ip)
            ->where('installation_domain', $installation_domain)
            ->first();

        if ($existingInstallation && (! empty($license_code) && $license_code != $existingInstallation->license_code || $this->validator->validateIntegerValue($client_id) && $client_id != $existingInstallation->user_id)) {
            $this->createReport($product_id, $license->user_id, $license_code, sprintf('Installation on %s (%s) belongs to another user', $installation_domain, $ip), 1);
            return $this->notificationResponse('notification_domain_in_use', []);
        }

        // Check installation limit
        if ($license->license_limit > 0) {
            $allInstallations = Installation::where('product_id', $product_id)
                ->where(function ($query) use ($client_id, $license_code): void {
                    $query->where('user_id', $client_id)
                        ->whereNotNull('user_id')
                        ->orWhere('license_code', $license_code);
                })
                ->count();

            if ($allInstallations > $license->license_limit) {
                $this->createReport($product_id, $license->user_id, $license_code, sprintf('Maximum installations limit (%s) exceeded', $license->license_limit), 1);

                return $this->notificationResponse('notification_license_limit', []);
            }
        }

        // Verify installation exists and is active (matching original: product + client/license + IP + domain + hash + status)
        $installation = Installation::where('product_id', $product_id)
            ->where(function ($query) use ($client_id, $license_code): void {
                $query->where('user_id', $client_id)
                    ->orWhere('license_code', $license_code);
            })
            ->where(function ($query) use ($ip): void {
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
