<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
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
        $ip = $request->ip();

        // Validate basic request
        if (! $this->validator->isValidLicenseRequest($ip, $product_id, $root_url)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Verify license
        $license = License::where('license_code', $license_code)->first();
        if (! $license) {
            return $this->notificationResponse('notification_license_not_found', []);
        }

        // Get scheme
        $scheme = LicenseScheme::where('scheme_status', 1)->first();
        if (! $scheme) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Log callback
        $this->createCallback($product_id, $license->user_id, $license_code, $ip, $root_url, 1);

        return $this->notificationResponse('notification_license_ok', [
            'scheme_id' => $scheme->id,
            'scheme_name' => 'license_scheme',
            'scheme_sql' => $scheme->scheme_query,
        ], $product_id, $client_email, $license_code, $root_url);
    }
}
