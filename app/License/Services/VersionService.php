<?php

namespace App\License\Services;

use App\Model\Product\ProductUpload;

class VersionService
{
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
}
