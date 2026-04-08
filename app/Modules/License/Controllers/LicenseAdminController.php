<?php

namespace App\Modules\License\Controllers;

use App\Modules\License\Services\LicenseService;
use App\Modules\License\Services\InstallationService;
use App\Modules\License\Services\VersionService;
use App\Modules\License\Models\License;
use App\Modules\License\Models\LicenseOption;
use App\Modules\License\Models\LicenseApiKey;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Lang;

/**
 * Admin license management controller.
 *
 * RESPONSE FORMAT: All methods return JSON using the same format
 * as the original license app:
 *
 * Success: { "success": true, "message": "...", "data": ... }
 * Error:   { "success": false, "message": "..." }
 *
 * Installation logs use the legacy format:
 * { "api_action_success": 1, "api_error_detected": 0, "action_success": 1, "error_detected": 0, "page_message": ... }
 */
class LicenseAdminController extends Controller
{
    protected LicenseService $licenseService;
    protected InstallationService $installationService;
    protected VersionService $versionService;

    public function __construct(
        LicenseService $licenseService,
        InstallationService $installationService,
        VersionService $versionService
    ) {
        $this->licenseService = $licenseService;
        $this->installationService = $installationService;
        $this->versionService = $versionService;
    }

    /**
     * Add a new license
     * Mirrors: POST /api/admin/license/add
     */
    public function create(Request $request)
    {
        $data = $request->all();

        // Validate required fields
        if (empty($data['product_id'])) {
            return $this->errorResponse('invalid_record_data', 400);
        }

        // Check for duplicate license code
        if (!empty($data['license_code'])) {
            $existing = License::where('license_code', $data['license_code'])->first();
            if ($existing) {
                return $this->errorResponse('error_client_or_license_code', 400);
            }
        }

        try {
            $license = $this->licenseService->create($data);

            $clientEmail = '';
            if ($license->user) {
                $clientEmail = $license->user->email ?? 'Unknown Client';
            }

            return $this->successResponse(Lang::get('lang.adddd', [], 'en'), [
                'license_code'  => $license->license_code,
                'client_email'  => $clientEmail ?: 'Unknown Client',
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('invalid', 400);
        }
    }

    /**
     * Update a license
     * Mirrors: POST /api/admin/license/edit
     */
    public function edit(Request $request)
    {
        $licenseId = $request->input('license_id');
        if (!$licenseId) {
            // Try to find by license_code
            $licenseCode = $request->input('license_code');
            $license = $licenseCode ? License::where('license_code', $licenseCode)->first() : null;
            $licenseId = $license ? $license->id : null;
        }

        if (!$licenseId) {
            return $this->errorResponse('license_id', 400);
        }

        $data = $request->only([
            'product_id', 'license_code', 'license_domain', 'license_ip',
            'license_require_domain', 'license_limit', 'license_status',
            'license_expire_date', 'license_updates_date', 'license_support_date',
            'license_order_number', 'license_comments',
        ]);

        try {
            $this->licenseService->update($licenseId, $data);

            $license = License::with('user')->find($licenseId);
            $clientEmail = $license->user->email ?? 'Unknown Client';

            return $this->successResponse(Lang::get('lang.license_Update', [], 'en'), [
                'license_code' => $license->license_code,
                'client_email' => $clientEmail,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('invalid', 400);
        }
    }

    /**
     * Deactivate a license
     * Mirrors: POST /api/admin/license/deactivate
     */
    public function deactivate(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $this->licenseService->deactivate($licenseCode);

        return $this->successResponse('License deactivated', 1);
    }

    /**
     * Reactivate a license
     */
    public function reactivate(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $this->licenseService->reactivate($licenseCode);

        return $this->successResponse('License reactivated', 1);
    }

    /**
     * Update license code
     * Mirrors: POST /api/admin/license/updateLicenseCode
     */
    public function updateLicenseCode(Request $request)
    {
        $oldCode = $request->input('old_license_code', $request->input('old_code'));
        $newCode = $request->input('license_code', $request->input('new_code'));

        $result = $this->licenseService->updateLicenseCode($oldCode, $newCode);

        return response()->json($result);
    }

    /**
     * Sync addon licenses
     * Mirrors: POST /api/admin/license/syncAddonLicense
     */
    public function syncAddonLicense(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $productIds  = $request->input('product_ids', []);
        $options     = $request->input('options', []);

        if (is_string($options)) {
            $options = json_decode($options, true) ?? [];
        }

        $this->licenseService->syncAddons($licenseCode, $productIds, $options);

        return $this->successResponse('Addon licenses synced', 1);
    }

    /**
     * Search licenses, products, clients, installations
     * Mirrors: POST /api/admin/search
     */
    public function search(Request $request)
    {
        $type    = $request->input('search_type', $request->input('type'));
        $keyword = $request->input('search_keyword', $request->input('keyword'));

        // Validate API key if provided
        $apiKeySecret = $request->input('api_key_secret');
        if ($apiKeySecret && !$this->validateApiKey($apiKeySecret)) {
            return $this->errorResponse('invalid_api_key', 400);
        }

        $results = $this->licenseService->search($type, $keyword);

        return response()->json(json_encode([
            'api_action_success' => 1,
            'api_error_detected' => 0,
            'action_success'     => 1,
            'error_detected'     => 0,
            'page_message'       => $results,
        ]));
    }

    /**
     * Get installation logs
     * Mirrors: POST /api/admin/getInstallationLogs
     */
    public function getInstallationLogs(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $result = $this->installationService->getLogs($licenseCode);

        return response()->json(json_encode($result));
    }

    /**
     * Update installation logs
     * Mirrors: POST /api/admin/updateInstallationLogs
     */
    public function updateInstallationLogs(Request $request)
    {
        $result = $this->installationService->updateLogs($request->all());

        return response()->json(json_encode($result));
    }

    /**
     * Get plugin licenses
     * Mirrors: GET /api/pluginLicense
     */
    public function pluginLicense(Request $request)
    {
        $licenseCodes = $request->input('license_code', $request->input('license_codes', []));

        if (is_string($licenseCodes)) {
            $licenseCodes = json_decode($licenseCodes, true) ?? [$licenseCodes];
        }

        $result = $this->licenseService->getPluginLicenses($licenseCodes);

        return $this->successResponse('', $result);
    }

    /**
     * Get license info with addons
     * Mirrors: GET /api/licenseInfo
     */
    public function licenseInfo(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $info = $this->licenseService->getLicenseInfo($licenseCode);

        if (!$info) {
            return $this->errorResponse('License not found', 404);
        }

        return $this->successResponse('license_info', $info);
    }

    /**
     * Get individual license info (options)
     * Mirrors: GET /api/IndividuallicenseInfo
     */
    public function individualLicenseInfo(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $info = $this->licenseService->getIndividualLicenseInfo($licenseCode);

        return $this->successResponse('', $info);
    }

    /**
     * Get order number from license
     * Mirrors: GET /api/getOrder
     */
    public function getOrder(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $orderNumber = $this->licenseService->getOrderNumber($licenseCode);

        return $this->successResponse('', $orderNumber);
    }

    /**
     * Reissue license for cloud
     * Mirrors: POST /api/LicenseReissue
     */
    public function reissueLicenseCloud(Request $request)
    {
        $licenseCode = $request->input('license_code');
        $this->licenseService->reissueLicenseCloud($licenseCode);

        return $this->successResponse('License reissued', 1);
    }

    /**
     * Get product ID by product key
     * Mirrors: GET /api/admin/getProductIdbyKey
     */
    public function getProductIdByKey(Request $request)
    {
        $productKey = $request->input('product_key');
        $product = Product::where('product_key', $productKey)->first();

        return response()->json($product ? $product->id : null);
    }

    /**
     * Get license by code
     */
    public function getByCode(string $licenseCode)
    {
        $license = $this->licenseService->findByCode($licenseCode);

        if (!$license) {
            return $this->errorResponse('License not found', 404);
        }

        return $this->successResponse('', $license);
    }

    /**
     * Get installations for a license
     */
    public function getInstallations(string $licenseCode)
    {
        $installations = $this->installationService->getByLicenseCode($licenseCode);

        return $this->successResponse('', $installations);
    }

    /**
     * Update installation
     * Mirrors: POST /api/admin/installations/edit
     */
    public function updateInstallation(Request $request)
    {
        $installationId = $request->input('installation_id');
        $data = $request->only(['installation_ip', 'installation_status']);

        $result = $this->installationService->update($installationId, $data);

        return response()->json(json_encode($result));
    }

    /**
     * Add installation for localized license
     * Mirrors: POST /api/admin/addInstallation
     */
    public function addInstallation(Request $request)
    {
        // Validate API key
        $apiKeySecret = $request->input('api_key_secret');
        if ($apiKeySecret && !$this->validateApiKey($apiKeySecret)) {
            return $this->errorResponse('invalid_api_key', 400);
        }

        $installation = $this->installationService->register($request->all());

        return $this->successResponse('install_added', 1);
    }

    // =========================================================================
    // RESPONSE HELPERS — match original license app format exactly
    // =========================================================================

    /**
     * Standard success response: { "success": true, "message": "...", "data": ... }
     */
    protected function successResponse(string $message = '', $data = '', int $statusCode = 200)
    {
        $response = ['success' => true];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        $response['data'] = $data;

        return response()->json($response, $statusCode);
    }

    /**
     * Standard error response: { "success": false, "message": "..." }
     */
    protected function errorResponse(string $message, int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode ?: 500);
    }

    /**
     * Validate API key
     */
    protected function validateApiKey(?string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }

        return LicenseApiKey::where('api_key_secret', $secret)
            ->where('api_key_status', 1)
            ->exists();
    }
}
