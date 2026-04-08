<?php

namespace App\Modules\License\Services;

use App\Modules\License\Models\ProductVersion;
use App\Modules\License\Models\VersionCallback;
use App\Modules\License\Models\VersionInstallation;
use Illuminate\Support\Collection;

class VersionService
{
    /**
     * Create a new product version.
     */
    public function create(array $data): ProductVersion
    {
        return ProductVersion::create([
            'product_id' => $data['product_id'],
            'version_number' => $data['version_number'],
            'version_install_file' => $data['version_install_file'] ?? null,
            'version_install_query' => $data['version_install_query'] ?? null,
            'version_raw_install_query' => $data['version_raw_install_query'] ?? null,
            'version_upgrade_file' => $data['version_upgrade_file'] ?? null,
            'version_upgrade_query' => $data['version_upgrade_query'] ?? null,
            'version_raw_upgrade_query' => $data['version_raw_upgrade_query'] ?? null,
            'version_install_limit' => $data['version_install_limit'] ?? null,
            'version_install_count' => 0,
            'version_upgrade_limit' => $data['version_upgrade_limit'] ?? null,
            'version_upgrade_count' => 0,
            'version_changelog' => $data['version_changelog'] ?? null,
            'version_date' => $data['version_date'] ?? now()->format('Y-m-d'),
            'version_expire_date' => $data['version_expire_date'] ?? null,
            'version_comments' => $data['version_comments'] ?? null,
            'version_status' => $data['version_status'] ?? 1,
        ]);
    }

    /**
     * Update a version.
     */
    public function update(int $id, array $data): bool
    {
        $version = ProductVersion::findOrFail($id);

        return $version->update($data);
    }

    /**
     * Get all versions for a product.
     */
    public function getByProductId(int $productId): Collection
    {
        return ProductVersion::where('product_id', $productId)
            ->orderBy('version_date', 'desc')
            ->get();
    }

    /**
     * Get latest active version for a product.
     */
    public function getLatestVersion(int $productId): ?ProductVersion
    {
        return ProductVersion::where('product_id', $productId)
            ->where('version_status', 1)
            ->orderBy('version_date', 'desc')
            ->first();
    }

    /**
     * Get version by number.
     */
    public function getVersionByNumber(int $productId, string $versionNumber): ?ProductVersion
    {
        return ProductVersion::where('product_id', $productId)
            ->where('version_number', $versionNumber)
            ->first();
    }

    /**
     * Get download file for version.
     */
    public function getDownloadFile(int $versionId): ?string
    {
        $version = ProductVersion::find($versionId);

        return $version ? $version->version_install_file : null;
    }

    /**
     * Get upgrade file for version.
     */
    public function getUpgradeFile(int $versionId): ?string
    {
        $version = ProductVersion::find($versionId);

        return $version ? $version->version_upgrade_file : null;
    }

    /**
     * Check if update is available.
     */
    public function isUpdateAvailable(int $productId, string $currentVersion): array
    {
        $latestVersion = $this->getLatestVersion($productId);

        if (! $latestVersion) {
            return [
                'available' => false,
                'message' => 'No versions found',
                'current_version' => $currentVersion,
            ];
        }

        $isAvailable = version_compare($latestVersion->version_number, $currentVersion, '>');

        return [
            'available' => $isAvailable,
            'latest_version' => $latestVersion->version_number,
            'current_version' => $currentVersion,
            'changelog' => $latestVersion->version_changelog,
            'install_file' => $isAvailable ? $latestVersion->version_install_file : null,
            'upgrade_file' => $isAvailable ? $latestVersion->version_upgrade_file : null,
        ];
    }

    /**
     * Register version installation.
     */
    public function registerInstallation(int $productId, int $versionId, string $ip, string $path = ''): VersionInstallation
    {
        return VersionInstallation::create([
            'product_id' => $productId,
            'version_id' => $versionId,
            'installation_ip' => $ip,
            'installation_path' => $path,
            'installation_date' => now()->format('Y-m-d'),
            'installation_status' => 1,
        ]);
    }

    /**
     * Log version callback.
     */
    public function logCallback(int $productId, int $versionId, string $type, ?string $ip = null, string $path = ''): VersionCallback
    {
        return VersionCallback::create([
            'product_id' => $productId,
            'version_id' => $versionId,
            'callback_type' => $type,
            'callback_ip' => $ip ?? request()->ip(),
            'callback_path' => $path,
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);
    }
}
