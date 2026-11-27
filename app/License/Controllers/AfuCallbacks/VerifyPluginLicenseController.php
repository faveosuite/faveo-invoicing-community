<?php

namespace App\License\Controllers\AfuCallbacks;

use App\License\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class VerifyPluginLicenseController extends Controller
{
    public function __construct(protected LicenseService $licenseService)
    {
    }

    /**
     * Faveo's "Install" on a compatible plugin: check the licence code the admin entered and, when it
     * is valid, hand out the plugin's product key so the client can download it.
     * POST /api/plugin/verifyLicense — license, product_id, plugin_id, plugin_license.
     */
    public function verify(Request $request): JsonResponse
    {
        // validated by hand: a failed $request->validate() redirects a client that didn't ask for JSON
        $validator = Validator::make($request->all(), [
            'license' => 'required|string',
            'product_id' => 'required|integer',
            'plugin_id' => 'required|integer',
            'plugin_license' => 'required|string',
        ]);

        if ($validator->fails()) {
            return errorResponse($validator->errors()->first(), 422);
        }

        $result = $this->licenseService->verifyCompatiblePluginLicense(
            (string) $request->input('license'),
            (int) $request->input('product_id'),
            (int) $request->input('plugin_id'),
            strtoupper(trim((string) $request->input('plugin_license'))),
        );

        if (isset($result['error'])) {
            return errorResponse($result['error'], 422);
        }

        return successResponse('', $result);
    }
}
