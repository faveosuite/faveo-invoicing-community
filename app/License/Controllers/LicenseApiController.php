<?php

namespace App\License\Controllers;

use App\License\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Minimal API controller for public license endpoints.
 * These routes are called by external deployed Faveo instances.
 */
class LicenseApiController extends Controller
{
    public function __construct(protected LicenseService $licenseService) {}

    /**
     * GET /api/IndividuallicenseInfo.
     */
    public function individualLicenseInfo(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');
        $info = $this->licenseService->getIndividualLicenseInfo($licenseCode);

        // Always send "data", even empty: installed products read $response['data'] unguarded,
        // and successResponse() drops an empty payload.
        return response()->json(['success' => true, 'data' => $info]);
    }

    /**
     * GET /api/getOrder.
     */
    public function getOrder(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');
        $orderNumber = $this->licenseService->getOrderNumber($licenseCode);

        // Same as above: AcceptorBilling reads $response['data'] unguarded, so keep it when null.
        return response()->json(['success' => true, 'data' => $orderNumber]);
    }

    /**
     * POST /api/LicenseReissue.
     */
    public function reissueLicenseCloud(Request $request): JsonResponse
    {
        $licenseCode = $request->input('license_code');
        $this->licenseService->reissueLicenseCloud($licenseCode);

        return successResponse('License reissued', 1);
    }
}
