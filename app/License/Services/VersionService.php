<?php

namespace App\License\Services;

use App\License\Models\VersionCallback;
use App\License\Models\VersionInstallation;
use App\Model\Product\ProductUpload;
use Illuminate\Support\Collection;

class VersionService
{
    public function create(array $data): ProductUpload
    {
        return ProductUpload::create([
            'product_id' => $data['product_id'],
            'title' => $data['title'] ?? ($data['version_number'] ?? $data['version'] ?? ''),
            'description' => $data['version_changelog'] ?? $data['description'] ?? null,
            'version' => $data['version_number'] ?? $data['version'],
            'file' => $data['version_install_file'] ?? $data['file'] ?? null,
            'release_type' => $data['release_type'] ?? 'official',
            'is_private' => $data['is_private'] ?? 0,
            'is_restricted' => $data['is_restricted'] ?? 0,
            'version_expire_date' => $data['version_expire_date'] ?: null,
            'version_install_count' => 0,
            'status' => $data['status'] ?? 1,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $version = ProductUpload::findOrFail($id);

        return $version->update($data);
    }

    public function getByProductId(int $productId): Collection
    {
        return ProductUpload::where('product_id', $productId)
            ->latest()
            ->get();
    }

    public function getLatestVersion(int $productId): ?ProductUpload
    {
        return ProductUpload::where('product_id', $productId)
            ->active()
            ->latest()
            ->first();
    }

    public function getVersionByNumber(int $productId, string $versionNumber): ?ProductUpload
    {
        return ProductUpload::where('product_id', $productId)
            ->where('version', $versionNumber)
            ->first();
    }

    public function getDownloadFile(int $versionId): ?string
    {
        $version = ProductUpload::find($versionId);

        return $version ? $version->file : null;
    }

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

        $isAvailable = version_compare($latestVersion->version, $currentVersion, '>');

        return [
            'available' => $isAvailable,
            'latest_version' => $latestVersion->version,
            'current_version' => $currentVersion,
            'changelog' => $latestVersion->description,
            'install_file' => $isAvailable ? $latestVersion->file : null,
        ];
    }

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
