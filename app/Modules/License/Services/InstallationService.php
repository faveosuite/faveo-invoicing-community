<?php

namespace App\Modules\License\Services;

use App\Modules\License\Models\Installation;
use App\Modules\License\Models\InstallationLog;
use Illuminate\Support\Collection;

class InstallationService
{
    /**
     * Register a new installation
     */
    public function register(array $data): Installation
    {
        return Installation::updateOrCreate(
            ['license_code' => $data['license_code']],
            [
                'product_id'                       => $data['product_id'],
                'user_id'                          => $data['user_id'],
                'installation_ip'                  => $data['installation_ip'] ?? request()->ip(),
                'installation_domain'              => $data['installation_domain'] ?? null,
                'installation_date'                => $data['installation_date'] ?? now()->format('Y-m-d'),
                'installation_status'              => $data['installation_status'] ?? 1,
                'installation_hash'                => $data['installation_hash'] ?? null,
                'installation_disable_ip_verification' => $data['installation_disable_ip_verification'] ?? 0,
            ]
        );
    }

    /**
     * Update an installation
     * Mirrors: POST /api/admin/installations/edit
     * Returns the same format as original
     */
    public function update(int $id, array $data): array
    {
        $installation = Installation::find($id);
        if (!$installation) {
            return [
                'api_action_success' => 0,
                'api_error_detected' => 1,
                'action_success'     => 0,
                'error_detected'     => 1,
                'page_message'       => 'Installation not found',
            ];
        }

        $updated = $installation->update($data);

        return [
            'api_action_success' => $updated ? 1 : 0,
            'api_error_detected' => $updated ? 0 : 1,
            'action_success'     => $updated ? 1 : 0,
            'error_detected'     => $updated ? 0 : 1,
            'page_message'       => $updated ? 'Installation updated successfully' : 'Failed to update installation',
        ];
    }

    /**
     * Update installations by license code (set inactive)
     */
    public function updateByLicenseCode(string $licenseCode, array $data): bool
    {
        return Installation::where('license_code', $licenseCode)->update($data) > 0;
    }

    /**
     * Reissue: delete installations for a license code
     * Mirrors: POST /api/admin/installation/reissue
     */
    public function reissue(string $installationPath): bool
    {
        return Installation::where('installation_domain', $installationPath)->delete() > 0;
    }

    /**
     * Get installation logs for a license
     * Mirrors: POST /api/admin/getInstallationLogs
     * Returns same format as original
     */
    public function getLogs(string $licenseCode): array
    {
        $logs = InstallationLog::where('license_code', $licenseCode)
            ->orderBy('installation_last_active_date', 'desc')
            ->get()
            ->toArray();

        return [
            'api_action_success' => 1,
            'api_error_detected' => 0,
            'action_success'     => 1,
            'error_detected'     => 0,
            'page_message'       => $logs,
        ];
    }

    /**
     * Update installation logs
     * Mirrors: POST /api/admin/updateInstallationLogs
     * Returns same format as original
     */
    public function updateLogs(array $data): array
    {
        $domain = $data['root_url'] ?? $data['installation_domain'] ?? '';
        // Extract raw domain
        if (!empty($domain)) {
            $scheme = parse_url($domain, PHP_URL_SCHEME);
            if (empty($scheme)) {
                $domain = 'http://' . $domain;
            }
            $domain = str_ireplace('www.', '', parse_url($domain, PHP_URL_HOST) ?? $domain);
        }

        $log = InstallationLog::updateOrCreate(
            [
                'license_code'        => $data['license_code'],
                'installation_domain' => $domain,
            ],
            [
                'version_number'              => $data['version_number'] ?? null,
                'installation_ip'             => $data['installation_ip'] ?? request()->ip(),
                'installation_status'         => 1,
                'installation_last_active_date' => now(),
            ]
        );

        return [
            'api_action_success' => 1,
            'api_error_detected' => 0,
            'action_success'     => 1,
            'error_detected'     => 0,
            'page_message'       => 'Installation Logs updated successfully',
        ];
    }

    /**
     * Get installations for a license
     */
    public function getByLicenseCode(string $licenseCode): Collection
    {
        return Installation::where('license_code', $licenseCode)
            ->with(['product', 'user'])
            ->get();
    }

    /**
     * Get installations for a user
     */
    public function getByUserId(int $userId): Collection
    {
        return Installation::where('user_id', $userId)
            ->with(['product'])
            ->get();
    }

    /**
     * Deactivate an installation
     */
    public function deactivate(int $installationId): bool
    {
        return Installation::where('id', $installationId)
            ->update(['installation_status' => 0]) > 0;
    }

    /**
     * Count active installations for a license
     */
    public function countActiveInstallations(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)
            ->where('installation_status', 1)
            ->count();
    }

    /**
     * Delete all installations for a license code
     */
    public function deleteByLicenseCode(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)->delete();
    }

    /**
     * Remove unwanted installations (inactive/old)
     */
    public function removeUnwanted(string $licenseCode, int $keepActive = 0): int
    {
        $query = Installation::where('license_code', $licenseCode)
            ->where('installation_status', 0);

        return $query->delete();
    }
}
