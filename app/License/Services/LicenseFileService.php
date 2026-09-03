<?php

namespace App\License\Services;

use App\License\Models\License;
use App\License\Models\LicenseScheme;
use App\Model\Order\Order;
use App\Model\Product\Product;
use SodiumException;

/**
 * Builds the signed, offline-verifiable license file ("license.json") for an
 * order — the same field set the online AFL license_verify callback returns,
 * so a licensed product can parse a local file identically to a live
 * response. Used both by the customer-facing localized-license download
 * (LocalizedLicenseController) and by product zip downloads that need to
 * embed a customer's own license file (ProductBundleStampingService).
 */
class LicenseFileService
{
    public function __construct(protected Ed25519SigningService $signingService)
    {
    }

    /**
     * Builds the license file for whichever product is being downloaded —
     * the order's own main product, or one of its attached plugins/add-ons.
     * Returns null when there's no license record for this order, or (for a
     * plugin) when that product isn't actually attached to the license.
     *
     * @throws SodiumException
     */
    public function buildForOrder(Order $order, Product $product): ?string
    {
        $license = License::where('license_order_number', $order->number)->first();

        if (! $license instanceof License) {
            return null;
        }

        $pluginProductId = $product->id === $license->product_id ? null : $product->id;

        return $this->buildSignedLicenseFile($license, $pluginProductId);
    }

    /**
     * Builds and signs the offline license file for a specific license
     * record, so it can be verified locally without any internet
     * interaction and never needs to be persisted anywhere. Returns null
     * when there is no matching scheme to build it from. Pass
     * $pluginProductId to build the file for one of the license's attached
     * plugins/add-ons instead of the main product.
     *
     * @throws SodiumException
     */
    public function buildSignedLicenseFile(License $license, ?int $pluginProductId = null): ?string
    {
        $payload = $this->buildLicensePayload($license, $pluginProductId);

        if ($payload === false) {
            return null;
        }

        $file = json_encode([
            'license' => $payload,
            'signature' => $this->signingService->sign($payload),
        ]);

        return $file === false ? null : $file;
    }

    /**
     * Same field set returned by the online AFL license_verify callback, so the
     * licensed product can parse a local file identically to a live response.
     *
     * With $pluginProductId set, builds the payload for that attached plugin
     * instead: product_id becomes the plugin's own ID, and the scheme_query
     * carries both the create and update table schemes (scheme_id 2 and 3) —
     * the client decides at install time whether its local plugin_license
     * table already exists. Fails if the product isn't actually attached to
     * this license.
     */
    private function buildLicensePayload(License $license, ?int $pluginProductId = null): string|false
    {
        if ($pluginProductId !== null) {
            if (! $license->addonProducts()->where('products.id', $pluginProductId)->exists()) {
                return false;
            }

            $createScheme = LicenseScheme::where('id', 2)->where('scheme_status', 1)->first();
            $updateScheme = LicenseScheme::where('id', 3)->where('scheme_status', 1)->first();

            if (! $createScheme || ! $updateScheme) {
                return false;
            }

            $productId = $pluginProductId;
            $schemeQuery = [
                'plugin_create_schema' => base64_encode($createScheme->scheme_query),
                'plugin_update_schema' => base64_encode($updateScheme->scheme_query),
            ];
        } else {
            // scheme_id 1 = product_schema, matching the online /api/licenseScheme flow's
            // schemeId for a non-plugin install (see LicenseSchemeController::licenseScheme).
            $scheme = LicenseScheme::where('id', 1)->where('scheme_status', 1)->first();

            if (! $scheme) {
                return false;
            }

            $productId = $license->product_id;
            $schemeQuery = ['product_schema' => base64_encode($scheme->scheme_query)];
        }

        return json_encode([
            'license_code' => $license->license_code,
            'product_id' => $productId,
            'license_status' => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain' => $license->license_domain,
            'license_ip' => $license->license_ip,
            'license_machine_id' => $license->license_machine_id,
            'scheme_query' => $schemeQuery,
        ]);
    }
}
