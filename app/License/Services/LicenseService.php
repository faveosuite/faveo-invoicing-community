<?php

namespace App\License\Services;

use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseOption;
use App\License\Models\LicensePlugin;
use App\Model\Product\ProductUpload;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    /**
     * Create a new license
     * Mirrors: POST /api/admin/license/add.
     *
     * @param  array<mixed>  $data
     */
    public function create(array $data): License
    {
        return DB::transaction(fn () => License::create([
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
        ]));
    }

    /**
     * Update an existing license
     * Mirrors: POST /api/admin/license/edit.
     *
     * @param  array<mixed>  $data
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
     * Deactivate a license
     * Mirrors: POST /api/admin/license/deactivate.
     */
    public function deactivate(string $licenseCode): bool
    {
        return (bool) License::where('license_code', $licenseCode)
            ->update(['license_status' => 0]);
    }

    /**
     * Get plugin licenses for given license codes
     * Mirrors: GET /api/pluginLicense.
     *
     * @param  array<mixed>  $licenseCodes
     * @return array<mixed>
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
                // @phpstan-ignore if.alwaysTrue (BelongsTo may return null for orphaned product_id)
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
     *
     * @param  array<mixed>  $options
     * @param  array<mixed>  $productIds
     */
    public function syncAddons(string $licenseCode, array $productIds, array $options = []): void
    {
        $license = License::where('license_code', $licenseCode)->firstOrFail();
        $licenseId = $license->id;

        // Filter out empty values and deduplicate
        $productIds = array_unique(array_filter($productIds, fn ($id): bool => ! empty($id)));

        DB::transaction(function () use ($licenseId, $productIds, $options): void {
            // Insert or update license plugins (upsert like original)
            foreach ($productIds as $productId) {
                LicensePlugin::updateOrCreate(
                    ['license_id' => $licenseId, 'product_id' => $productId],
                    ['license_id' => $licenseId, 'product_id' => $productId]
                );
            }

            // Insert or update license options (upsert like original)
            foreach ($options as $option) {
                $key = $option['key'] ?? $option['option_key'] ?? '';
                if ($key === '') {
                    continue;
                }

                LicenseOption::updateOrCreate(
                    [
                        'option_key' => $key,
                        'option_group' => (string) $licenseId,
                    ],
                    [
                        'option_value' => (string) ($option['value'] ?? $option['option_value'] ?? ''),
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
     * Get license info with addons
     * Mirrors: GET /api/licenseInfo.
     *
     * @return array<mixed>
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

        $licenseOptions = LicenseOption::where('option_group', (string) $license->id)
            ->get()
            ->pluck('option_value', 'option_key')
            ->toArray();

        $addons = [];
        foreach ($license->plugins as $plugin) {
            $product = $plugin->product;
            // @phpstan-ignore if.alwaysTrue (BelongsTo may return null for orphaned product_id)
            if ($product) {
                $latestVersion = $latestVersions->get($product->id);

                $addons[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? $product->product_title ?? '',
                    'product_attributes' => [],
                    'product_attributes_license' => $licenseOptions,
                    'latest_version' => $latestVersion ? $latestVersion->version : null,
                    'latest_version_file' => $latestVersion ? $latestVersion->file : null,
                ];
            }
        }

        return [
            'license' => $license->toArray(),
            'product' => $license->product ? $license->product->toArray() : null, // @phpstan-ignore ternary.alwaysTrue
            'addons' => $addons,
        ];
    }

    /**
     * Get individual license info (options)
     * Mirrors: GET /api/IndividuallicenseInfo.
     *
     * @return array<mixed>
     */
    public function getIndividualLicenseInfo(string $licenseCode): array
    {
        $license = License::where('license_code', $licenseCode)->first();
        if (! $license) {
            return [];
        }

        return LicenseOption::where('option_group', (string) $license->id)
            ->get()
            ->map(fn ($option): array => [
                'license_code' => $license->license_code,
                'id' => $option->id,
                'option_group' => $option->option_group,
                'key' => $option->option_key,
                'value' => $option->option_value,
            ])
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
     * Reissue license (delete installations for cloud re-issue)
     * Mirrors: POST /api/LicenseReissue.
     */
    public function reissueLicenseCloud(string $licenseCode): int
    {
        return Installation::where('license_code', $licenseCode)->delete();
    }

    /**
     * Parse a domain string into IP/domain components for license assignment.
     *
     * @return array<mixed>
     */
    public static function parseIpAndDomain(string $domain): array
    {
        if ($domain !== '') {
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
            $code = strtoupper(substr(md5(uniqid((string) random_int(0, mt_getrandmax()), more_entropy: true)), 0, 16));
            $code = chunk_split($code, 4, '-');
            $code = substr($code, 0, -1);
        } while (License::where('license_code', $code)->exists());

        return $code;
    }
}
