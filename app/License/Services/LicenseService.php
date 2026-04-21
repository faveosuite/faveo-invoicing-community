<?php

namespace App\License\Services;

use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseOption;
use App\License\Models\LicensePlugin;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    /**
     * Create a new license
     * Mirrors: POST /api/admin/license/add.
     */
    public function create(array $data): License
    {
        return DB::transaction(function () use ($data) {
            return License::create([
                'product_id' => $data['product_id'],
                'user_id' => $data['user_id'] ?? $data['client_id'] ?? null,
                'license_code' => $data['license_code'] ?? $this->generateLicenseCode(),
                'license_order_number' => $data['license_order_number'] ?? null,
                'license_domain' => $data['license_domain'] ?? null,
                'license_ip' => $data['license_ip'] ?? null,
                'license_require_domain' => $data['license_require_domain'] ?? 0,
                'license_expire_date' => $data['license_expire_date'] ?? null,
                'license_expire_email_date' => null,
                'license_updates_date' => $data['license_updates_date'] ?? null,
                'license_updates_email_date' => null,
                'license_support_date' => $data['license_support_date'] ?? null,
                'license_support_email_date' => null,
                'license_limit' => $data['license_limit'] ?? 1,
                'license_status' => $data['license_status'] ?? 1,
                'license_date' => $data['license_date'] ?? now()->format('Y-m-d'),
                'license_cancel_date' => null,
                'license_comments' => $data['license_comments'] ?? null,
            ]);
        });
    }

    /**
     * Update an existing license
     * Mirrors: POST /api/admin/license/edit.
     */
    public function update(int $id, array $data): bool
    {
        $license = License::findOrFail($id);

        // Reset email notification dates when expiry dates change
        if (isset($data['license_expire_date']) && $data['license_expire_date'] != $license->license_expire_date) {
            $data['license_expire_email_date'] = null;
        }
        if (isset($data['license_updates_date']) && $data['license_updates_date'] != $license->license_updates_date) {
            $data['license_updates_email_date'] = null;
        }
        if (isset($data['license_support_date']) && $data['license_support_date'] != $license->license_support_date) {
            $data['license_support_email_date'] = null;
        }

        // Set cancel date when status changes to inactive
        if (isset($data['license_status']) && (int) $data['license_status'] === 0 && $license->license_status !== 0) {
            $data['license_cancel_date'] = now()->format('Y-m-d');
        }

        return $license->update($data);
    }

    /**
     * Update license by license code.
     */
    public function updateByCode(string $licenseCode, array $data): bool
    {
        $license = License::where('license_code', $licenseCode)->first();
        if (! $license) {
            return false;
        }

        return $this->update($license->id, $data);
    }

    /**
     * Deactivate a license
     * Mirrors: POST /api/admin/license/deactivate.
     */
    public function deactivate(string $licenseCode): bool
    {
        return License::where('license_code', $licenseCode)
            ->update(['license_status' => 0]);
    }

    /**
     * Reactivate a license.
     */
    public function reactivate(string $licenseCode): bool
    {
        return License::where('license_code', $licenseCode)
            ->update([
                'license_status' => 1,
                'license_cancel_date' => null,
            ]);
    }

    /**
     * Search licenses, products, clients, or installations
     * Mirrors: POST /api/admin/search.
     */
    public function search(string $type, string $keyword): array
    {
        return match ($type) {
            'license' => License::where('license_code', 'like', "%{$keyword}%")
                ->orWhere('license_domain', 'like', "%{$keyword}%")
                ->orWhere('license_order_number', 'like', "%{$keyword}%")
                ->with(['product', 'user'])
                ->get()
                ->toArray(),
            'product' => Product::where('name', 'like', "%{$keyword}%")
                ->orWhere('product_sku', 'like', "%{$keyword}%")
                ->get()
                ->toArray(),
            'client' => User::where('email', 'like', "%{$keyword}%")
                ->orWhere('first_name', 'like', "%{$keyword}%")
                ->orWhere('last_name', 'like', "%{$keyword}%")
                ->get()
                ->toArray(),
            'installation' => Installation::where('installation_domain', 'like', "%{$keyword}%")
                ->orWhere('license_code', 'like', "%{$keyword}%")
                ->orWhere('installation_ip', 'like', "%{$keyword}%")
                ->with(['product', 'user'])
                ->get()
                ->toArray(),
            default => [],
        };
    }

    /**
     * Get plugin licenses for given license codes
     * Mirrors: GET /api/pluginLicense.
     */
    public function getPluginLicenses(array $licenseCodes): array
    {
        $licenses = License::with(['plugins.product'])
            ->whereIn('license_code', $licenseCodes)
            ->get();

        $productIds = $licenses->flatMap(fn ($l) => $l->plugins->pluck('product_id'))->unique()->filter()->values();

        $latestVersions = ProductUpload::whereIn('product_id', $productIds)
            ->active()
            ->orderByDesc('id')
            ->get()
            ->unique('product_id')
            ->keyBy('product_id');

        $result = [];
        foreach ($licenses as $license) {
            foreach ($license->plugins as $plugin) {
                $product = $plugin->product;
                if ($product) {
                    $latestVersion = $latestVersions->get($product->id);
                    $result[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name ?? $product->product_title ?? '',
                        'product_sku' => $product->product_sku ?? '',
                        'latest_version' => $latestVersion ? $latestVersion->version : null,
                        'latest_version_file' => $latestVersion ? $latestVersion->file : null,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Update license code
     * Mirrors: POST /api/admin/license/updateLicenseCode.
     */
    public function updateLicenseCode(string $oldCode, string $newCode): int
    {
        return License::where('license_code', $oldCode)
            ->update(['license_code' => $newCode]);
    }

    /**
     * Sync addon licenses for a parent license
     * Mirrors: POST /api/admin/license/syncAddonLicense.
     */
    public function syncAddons(string $licenseCode, array $productIds, array $options = []): void
    {
        $license = License::where('license_code', $licenseCode)->firstOrFail();
        $licenseId = $license->id;

        // Filter out empty values and deduplicate
        $productIds = array_unique(array_filter($productIds, fn ($id) => ! empty($id)));

        DB::transaction(function () use ($license, $licenseId, $productIds, $options) {
            // Insert or update license plugins (upsert like original)
            foreach ($productIds as $productId) {
                LicensePlugin::updateOrCreate(
                    ['license_id' => $licenseId, 'product_id' => $productId],
                    ['license_id' => $licenseId, 'product_id' => $productId]
                );
            }

            // Insert or update license options (upsert like original)
            foreach ($options as $option) {
                LicenseOption::updateOrCreate(
                    [
                        'license_id' => $licenseId,
                        'product_id' => $option['product_id'] ?? $license->product_id,
                        'option_group' => $option['option_group'] ?? '',
                        'option_name' => $option['option_name'] ?? '',
                        'key' => $option['key'] ?? '',
                    ],
                    [
                        'value' => $option['value'] ?? '',
                    ]
                );
            }
        });
    }

    /**
     * Find license by code.
     */
    public function findByCode(string $licenseCode): ?License
    {
        return License::where('license_code', $licenseCode)
            ->with(['product', 'user'])
            ->first();
    }

    /**
     * Get all licenses for a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return License::where('user_id', $userId)
            ->with(['product'])
            ->get();
    }

    /**
     * Get all licenses for a product.
     */
    public function getByProductId(int $productId): Collection
    {
        return License::where('product_id', $productId)
            ->with(['user'])
            ->get();
    }

    /**
     * Get license info with addons
     * Mirrors: GET /api/licenseInfo.
     */
    public function getLicenseInfo(string $licenseCode): ?array
    {
        $license = License::where('license_code', $licenseCode)
            ->with(['product', 'user', 'plugins.product'])
            ->first();

        if (! $license) {
            return null;
        }

        $pluginProductIds = $license->plugins->pluck('product_id')->unique()->filter()->values();

        $latestVersions = ProductUpload::whereIn('product_id', $pluginProductIds)
            ->active()
            ->orderByDesc('id')
            ->get()
            ->unique('product_id')
            ->keyBy('product_id');

        $allOptions = LicenseOption::where('license_id', $license->id)
            ->whereIn('product_id', $pluginProductIds)
            ->get()
            ->groupBy('product_id');

        $addons = [];
        foreach ($license->plugins as $plugin) {
            $product = $plugin->product;
            if ($product) {
                $latestVersion = $latestVersions->get($product->id);
                $options = $allOptions->get($product->id, collect());

                $addons[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? $product->product_title ?? '',
                    'product_attributes' => $options->where('option_group', 'attributes')->pluck('value', 'key')->toArray(),
                    'product_attributes_license' => $options->where('option_group', 'license_attributes')->pluck('value', 'key')->toArray(),
                    'latest_version' => $latestVersion ? $latestVersion->version : null,
                    'latest_version_file' => $latestVersion ? $latestVersion->file : null,
                ];
            }
        }

        return [
            'license' => $license->toArray(),
            'product' => $license->product ? $license->product->toArray() : null,
            'addons' => $addons,
        ];
    }

    /**
     * Get individual license info (options)
     * Mirrors: GET /api/IndividuallicenseInfo.
     */
    public function getIndividualLicenseInfo(string $licenseCode): array
    {
        $license = License::where('license_code', $licenseCode)->first();
        if (! $license) {
            return [];
        }

        return LicenseOption::where('license_id', $license->id)
            ->get()
            ->map(function ($option) use ($license) {
                return [
                    'license_code' => $license->license_code,
                    'product_id' => $option->product_id,
                    'option_group' => $option->option_group,
                    'option_name' => $option->option_name,
                    'key' => $option->key,
                    'value' => $option->value,
                ];
            })
            ->toArray();
    }

    /**
     * Get order number from license code
     * Mirrors: GET /api/getOrder.
     */
    public function getOrderNumber(string $licenseCode): ?string
    {
        return License::where('license_code', $licenseCode)
            ->value('license_order_number');
    }

    /**
     * Delete license with all related data.
     */
    public function deleteLicense(int $licenseId): bool
    {
        return DB::transaction(function () use ($licenseId) {
            $license = License::findOrFail($licenseId);

            // Delete related records
            \App\License\Models\LicenseCallback::where('license_code', $license->license_code)->delete();
            Installation::where('license_code', $license->license_code)->delete();
            \App\License\Models\InstallationLog::where('license_code', $license->license_code)->delete();
            LicensePlugin::where('license_id', $license->id)->delete();
            LicenseOption::where('license_id', $license->id)->delete();

            return $license->delete();
        });
    }

    /**
     * Reissue license (delete installations for cloud re-issue)
     * Mirrors: POST /api/LicenseReissue.
     */
    public function reissueLicenseCloud(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)->delete();
    }

    /**
     * Update expiration dates.
     */
    public function updateExpirationDates(string $licenseCode, array $dates): bool
    {
        $updateData = [];

        if (isset($dates['license_expire_date'])) {
            $updateData['license_expire_date'] = $dates['license_expire_date'];
            $updateData['license_expire_email_date'] = null;
        }
        if (isset($dates['license_updates_date'])) {
            $updateData['license_updates_date'] = $dates['license_updates_date'];
            $updateData['license_updates_email_date'] = null;
        }
        if (isset($dates['license_support_date'])) {
            $updateData['license_support_date'] = $dates['license_support_date'];
            $updateData['license_support_email_date'] = null;
        }

        if (empty($updateData)) {
            return false;
        }

        return License::where('license_code', $licenseCode)->update($updateData) > 0;
    }

    /**
     * Parse a domain string into IP/domain components for license assignment.
     */
    public static function parseIpAndDomain(string $domain): array
    {
        if ($domain != '') {
            if (ip2long($domain)) {
                return ['ip' => $domain, 'domain' => '', 'requireDomain' => 0];
            }

            return ['ip' => '', 'domain' => $domain, 'requireDomain' => 1];
        }

        return ['ip' => '', 'domain' => '', 'requireDomain' => 0];
    }

    /**
     * Generate a unique license code.
     */
    public function generateLicenseCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
            $code = chunk_split($code, 4, '-');
            $code = substr($code, 0, -1);
        } while (License::where('license_code', $code)->exists());

        return $code;
    }
}
