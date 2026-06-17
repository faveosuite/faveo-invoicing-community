<?php

namespace App\Http\Controllers\AutoUpdate;

use App\Http\Controllers\Controller;
use App\License\Services\VersionService;
use App\Model\Product\Product;
use Exception;

class AutoUpdateController extends Controller
{
    public function __construct(protected VersionService $versionService)
    {
    }

    /*
    *  Add New Version
    */
    public function addNewVersion($product_id, $version_number, $upgrade_zip_file, $version_status): void
    {
        $this->versionService->create([
            'product_id' => $product_id,
            'version_number' => $version_number,
            'version_upgrade_file' => $upgrade_zip_file,
            'version_status' => $version_status ?? 'active',
        ]);
    }

    /*
    *  Edit Version
    */
    public function editVersion(string $version_number, $product_sku): void
    {
        $product = Product::where('product_sku', $product_sku)->first();
        if (! $product) {
            throw new Exception(__('message.product_not_found'));
        }

        $version = $this->versionService->getVersionByNumber($product->id, $version_number);
        if (! $version instanceof \App\Model\Product\ProductUpload) {
            throw new Exception(__('message.version_not_found'));
        }

        // Version editing would require additional parameters
        // This method should be expanded with proper update logic
    }

    /*
    *  Search Version
    */
    public function searchVersion(string $version_number, $product_sku): array
    {
        $product = Product::where('product_sku', $product_sku)->first();
        if (! $product) {
            return ['version_id' => '', 'product_id' => ''];
        }

        $version = $this->versionService->getVersionByNumber($product->id, $version_number);

        return [
            'version_id' => $version instanceof \App\Model\Product\ProductUpload ? $version->id : '',
            'product_id' => $product->id,
        ];
    }
}
