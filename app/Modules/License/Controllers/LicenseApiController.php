<?php

namespace App\Modules\License\Controllers;

use App\Modules\License\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Minimal API controller for public license endpoints.
 * These routes are called by external deployed Faveo instances.
 */
class LicenseApiController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * GET /api/licenseInfo
     */
    public function licenseInfo(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $info = $this->licenseService->getLicenseInfo($licenseCode);

        if (!$info) {
            return errorResponse('License not found', 404);
        }

        return successResponse('license_info', $info);
    }

    /**
     * GET /api/IndividuallicenseInfo
     */
    public function individualLicenseInfo(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $info = $this->licenseService->getIndividualLicenseInfo($licenseCode);

        return successResponse('', $info);
    }

    /**
     * GET /api/getOrder
     */
    public function getOrder(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $orderNumber = $this->licenseService->getOrderNumber($licenseCode);

        return successResponse('', $orderNumber);
    }

    /**
     * GET|POST /api/pluginLicense
     */
    public function pluginLicense(Request $request)
    {
        $licenseCodes = $request->input('license_code', $request->input('license_codes', []));

        if (is_string($licenseCodes)) {
            $licenseCodes = json_decode($licenseCodes, true) ?? [$licenseCodes];
        }

        $result = $this->licenseService->getPluginLicenses($licenseCodes);

        return successResponse('', $result);
    }

    /**
     * POST /api/LicenseReissue
     */
    public function reissueLicenseCloud(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $this->licenseService->reissueLicenseCloud($licenseCode);

        return successResponse('License reissued', 1);
    }
}
