<?php

namespace App\License\Services;

use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use Illuminate\Support\Collection;

class InstallationService
{
    /**
     * Register a new installation.
     * Original matches on product_id + license_code/client_id + IP + domain + hash.
     * Uses product_id + installation_ip + installation_domain as the unique key
     * so the same license can have multiple installations on different domains.
     */
    public function register(array $data): Installation
    {
        return Installation::updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'license_code' => $data['license_code'],
                'installation_ip' => $data['installation_ip'] ?? request()->ip(),
                'installation_domain' => $data['installation_domain'] ?? null,
            ],
            [
                'user_id' => $data['user_id'],
                'installation_date' => $data['installation_date'] ?? now()->format('Y-m-d'),
                'installation_status' => $data['installation_status'] ?? 1,
                'installation_hash' => $data['installation_hash'] ?? null,
            ]
        );
    }

    /**
     * Update installation logs
     * Mirrors: POST /api/admin/updateInstallationLogs
     * Returns same format as original.
     */
    public function updateLogs(array $data): array
    {
        $domain = $data['root_url'] ?? $data['installation_domain'] ?? '';
        // Extract raw domain
        if (! empty($domain)) {
            $scheme = parse_url($domain, PHP_URL_SCHEME);
            if (empty($scheme)) {
                $domain = 'http://'.$domain;
            }
            $domain = str_ireplace('www.', '', parse_url($domain, PHP_URL_HOST) ?? $domain);
        }

        $log = InstallationLog::updateOrCreate(
            [
                'license_code' => $data['license_code'],
                'installation_domain' => $domain,
            ],
            [
                'version_number' => $data['version_number'] ?? null,
                'installation_ip' => $data['installation_ip'] ?? request()->ip(),
                'installation_status' => 1,
                'installation_last_active_date' => now(),
            ]
        );

        return [
            'api_action_success' => 1,
            'api_error_detected' => 0,
            'action_success' => 1,
            'error_detected' => 0,
            'page_message' => 'Installation Logs updated successfully',
        ];
    }

    /**
     * Get installations for a license.
     */
    public function countActiveInstallations(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)
            ->where('installation_status', 1)
            ->count();
    }

    public function getByLicenseCode(string $licenseCode): Collection
    {
        return Installation::where('license_code', $licenseCode)
            ->with(['product', 'user'])
            ->get();
    }

    /**
     * Remove every installation tied to a license.
     * Used on reissue so the install slots are freed and the user can
     * re-install on a new domain — the install limit check counts rows
     * regardless of installation_status, so they must be deleted, not deactivated.
     *
     * @return int Number of deleted rows.
     */
    public function deleteByLicenseCode(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)->delete();
    }

    /**
     * Get installation details for a license filtered by product, formatted as arrays.
     */
    public function getInstallationsByProduct(string $licenseCode, int $productId): array
    {
        $installations = $this->getByLicenseCode($licenseCode);

        $domains = [];
        $ips = [];
        $dates = [];
        $statuses = [];

        foreach ($installations as $detail) {
            if ($detail->product_id == $productId) {
                $domains[] = $detail->installation_domain;
                $ips[] = $detail->installation_ip;
                $dates[] = $detail->installation_date;
                $statuses[] = $detail->installation_status;
            }
        }

        return [
            'installed_path' => $domains,
            'installed_ip' => $ips,
            'installation_date' => $dates,
            'installation_status' => $statuses,
        ];
    }
}
