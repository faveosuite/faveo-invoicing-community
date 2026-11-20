<?php

namespace App\License\Services;

use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicensePlugin;
use Illuminate\Support\Facades\DB;

/**
 * Moves licenses from a retired product onto the product it was merged into (Faveo
 * v9.4.3.9.B.2 merged each plugin's "Pro" and non-Pro products — config/merged_products.php).
 *
 * Only reached when the license is not found for the requested product the normal way, so
 * a request that already matches is never touched. Two callers end up here:
 *  - a B.2+ Faveo, which sends the current product_id plus the plugin's old_product_id: the
 *    license is moved once, and marked (migrated_from_product_id) so it is never moved again;
 *  - an older Faveo, which still sends the retired product_id: served from that mark, so a
 *    site that hasn't updated keeps working after another site on the same license has.
 */
class ProductMergeService
{
    public function resolve(string $licenseCode, int $productId, ?int $oldProductId = null): ?License
    {
        return $oldProductId === null
            ? $this->findMigrated($licenseCode, $productId)
            : $this->migrate($licenseCode, $productId, $oldProductId);
    }

    /**
     * Move the license from $oldProductId onto $productId. old_product_id is not covered by the
     * request signature, so the pair must be one config/merged_products.php lists — otherwise any
     * license could be moved onto any product.
     */
    private function migrate(string $licenseCode, int $productId, int $oldProductId): ?License
    {
        if ((int) (config('merged_products.products')[$productId] ?? 0) !== $oldProductId) {
            return null;
        }

        return DB::transaction(function () use ($licenseCode, $productId, $oldProductId): ?License {
            // Locked so two requests for the same license can't both move it.
            $licenses = License::where('license_code', $licenseCode)->lockForUpdate()->get();

            // An add-on bought on its own: the license is the product's.
            $own = $licenses->first(fn (License $license): bool => $license->product_id === $oldProductId);
            if ($own) {
                $own->update(['product_id' => $productId, 'migrated_from_product_id' => $oldProductId, 'product_migrated_at' => now()]);
                $this->copyInstallations($licenseCode, $oldProductId, $productId);

                return $own;
            }

            // Moved by a request that held the lock before this one.
            $moved = $licenses->first(fn (License $license): bool => $license->product_id === $productId);
            if ($moved) {
                return $moved;
            }

            // A plugin bundled into the license: it's the link row that moves.
            foreach ($licenses as $license) {
                $links = LicensePlugin::where('license_id', $license->id)->whereIn('product_id', [$oldProductId, $productId])->get();
                $old = $links->firstWhere('product_id', $oldProductId);

                if (! $old && ! $links->firstWhere('product_id', $productId)) {
                    continue;
                }

                if ($old && ! $links->firstWhere('product_id', $productId)) {
                    $old->update(['product_id' => $productId, 'migrated_from_product_id' => $oldProductId, 'product_migrated_at' => now()]);
                    $this->copyInstallations($licenseCode, $oldProductId, $productId);
                }

                // Same as LicenseValidator::findLicenseWithPlugins for a plugin of this license.
                $license->product_id = $productId;

                return $license;
            }

            return null;
        });
    }

    /**
     * A license already moved off $productId (the retired product an older Faveo still asks for).
     */
    private function findMigrated(string $licenseCode, int $productId): ?License
    {
        $license = License::where('license_code', $licenseCode)->where('migrated_from_product_id', $productId)->first()
            ?? License::where('license_code', $licenseCode)
                ->whereIn('id', LicensePlugin::where('migrated_from_product_id', $productId)->select('license_id'))
                ->first();

        if (! $license) {
            return null;
        }

        // Answer for the product that was asked for, as findLicenseWithPlugins does for a plugin.
        $license->product_id = $productId;

        return $license;
    }

    /**
     * Copied, not moved: the licensing callbacks look installations up by the product id the
     * client sends, so the retired product's rows serve sites still on an older Faveo, and the
     * copies serve the updated ones without a reinstall.
     */
    private function copyInstallations(string $licenseCode, int $oldProductId, int $productId): void
    {
        $installations = Installation::where('license_code', $licenseCode)->where('product_id', $oldProductId)->get();

        foreach ($installations as $installation) {
            $exists = Installation::where('license_code', $licenseCode)
                ->where('product_id', $productId)
                ->where('installation_ip', $installation->installation_ip)
                ->where('installation_domain', $installation->installation_domain)
                ->exists();

            if (! $exists) {
                $copy = $installation->replicate();
                $copy->product_id = $productId;
                $copy->save();
            }
        }
    }
}
