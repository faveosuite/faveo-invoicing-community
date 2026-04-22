<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseScheme;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LicenseSchemeController extends Controller
{
    use AflCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get license scheme query
     * POST /apl_callbacks/license_scheme.php  OR  POST /api/licenseScheme.
     */
    public function licenseScheme(Request $request)
    {
        $product_id = $request->input('product_id');
        $root_url = $request->input('root_url');
        $client_email = $request->input('client_email');
        $license_code = $request->input('license_code');
        $installation_hash = $request->input('installation_hash');
        $license_signature = $request->input('license_signature');
        $client_id = $request->input('client_id');
        $isPlugin = $request->input('isPlugin', null);
        $tableCreate = $request->input('tableCreate', true);
        $ip = $this->validator->resolveIp($request);

        // Validate basic request
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url, $license_code, $client_email)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Validate installation hash
        if (! $this->validator->validateInstallationHash($installation_hash, $root_url, $client_email, $license_code)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Verify license signature
        if (! $this->validator->verifyScriptSignature($license_signature, $product_id, $root_url, $client_email, $license_code)) {
            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Verify product exists
        $product = $this->validator->validateProduct($product_id);
        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Find license (with LicensePlugin support)
        $license = $this->validator->findLicense($license_code, $client_email, $product_id);

        if (! $license) {
            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Validate license (status, expiry, IP, domain)
        $validation = $this->validator->validateLicense($license, $product_id, $client_email, $ip, $root_url);
        if (! $validation['valid']) {
            return $this->notificationResponse($this->mapErrorToNotification($validation['error']), []);
        }

        $license = $validation['license'];
        $installation_domain = $this->getInstallationDomain($root_url);

        // Verify active installation exists (original requires this before returning scheme)
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
            return $this->notificationResponse('notification_installation_not_found', []);
        }

        // Get scheme based on isPlugin and tableCreate parameters (matching original logic)
        // scheme_id 1 = standard, 2 = plugin with table create, 3 = plugin without table create
        $schemeId = empty($isPlugin) ? 1 : ($tableCreate ? 2 : 3);
        $scheme = LicenseScheme::where('id', $schemeId)
            ->where('scheme_status', 1)
            ->first();

        if (! $scheme) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        return $this->notificationResponse('notification_license_ok', [
            'scheme_id' => $scheme->id,
            'scheme_name' => 'license_scheme',
            'scheme_query' => $scheme->scheme_query,
        ], $product_id, $client_email, $license_code, $root_url);
    }
}
