<?php

namespace App\Services\Product;

use App\Facades\Attach;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use ZipArchive;

/**
 * Turns one canonical uploaded build into a specific product's own
 * downloadable file, by writing that product's identity (product_key,
 * a persistent salt, app name/version) into whatever destination path
 * *that product itself* declares (Product::config_file_path). This lets
 * one shared build — every plugin folder intact — be sold as many
 * differently-licensed products. Nothing is ever removed from the zip.
 */
class ProductBundleStampingService
{
    /**
     * Stamps $targetProduct's identity into a local temp copy of the
     * canonical build and returns that copy's path. Nothing is persisted
     * back to storage — the caller owns the returned file and must delete
     * it once done.
     *
     * @throws RuntimeException if the product has no product_key, or the
     *                          canonical file can't be found/opened.
     */
    public function stampToLocalFile(string $canonicalFilePath, Product $targetProduct, string $version): string
    {
        if (empty($targetProduct->product_key)) {
            // Nothing downstream can ever authenticate a build stamped with
            // no key — refuse rather than silently ship an unusable artifact.
            throw new RuntimeException("Product #{$targetProduct->id} ({$targetProduct->name}) has no product_key set — refusing to stamp a build for it.");
        }

        $localCopy = $this->copyToLocalTemp($canonicalFilePath);

        try {
            $this->stampZip($localCopy, $targetProduct, $version);
        } catch (Throwable $e) {
            // Don't leave a half-stamped (or never-stamped) copy behind in the
            // system temp dir — this runs on every download, so a recurring
            // failure here would otherwise accumulate real disk usage.
            @unlink($localCopy);

            throw $e;
        }

        return $localCopy;
    }

    /**
     * Stamps a copy of $storagePath for $product and returns it as a
     * ready-to-return file-download response (cleans up the temp file
     * after sending).
     *
     * @throws RuntimeException if stamping fails; callers decide what
     *                          that looks like in their own response.
     */
    public function downloadResponseFor(ProductUpload $version, Product $product, string $storagePath): Response
    {
        $localStampedPath = $this->stampToLocalFile($storagePath, $product, $version->version);

        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);
        $safeVersion = preg_replace('/[^A-Za-z0-9.]+/', '-', $version->version);
        $downloadName = Str::slug($product->name).'-'.$safeVersion.'.'.$extension;

        return response()->download($localStampedPath, $downloadName)->deleteFileAfterSend();
    }

    /**
     * Copies an Attach-stored file (local disk or remote/S3) to a local
     * temp path so ZipArchive has a real filesystem path to work with.
     *
     * @throws RuntimeException if the source file doesn't exist or the
     *                          temp file can't be allocated/opened.
     */
    private function copyToLocalTemp(string $canonicalFilePath): string
    {
        if (! Attach::exists($canonicalFilePath)) {
            throw new RuntimeException("Canonical build not found: {$canonicalFilePath}");
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'product_bundle_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to allocate a local temp file for stamping.');
        }

        // Open the destination before the (potentially remote/S3) source
        // stream, so a local fopen failure never leaves a source stream open.
        $destination = fopen($tempPath, 'wb');

        if ($destination === false) {
            throw new RuntimeException("Unable to open temp file for writing: {$tempPath}");
        }

        $source = Attach::readStream($canonicalFilePath);
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);

        return $tempPath;
    }

    /**
     * Does the actual in-place stamping of the local zip copy. Everything
     * else in the zip, including every bundled plugin folder, is left
     * untouched.
     */
    private function stampZip(string $localZipPath, Product $targetProduct, string $version): void
    {
        $zip = new ZipArchive;

        if ($zip->open($localZipPath) !== true) {
            throw new RuntimeException("Unable to open canonical build for stamping: {$localZipPath}");
        }

        try {
            if (! empty($targetProduct->config_file_path)) {
                $this->assertSafeZipEntryPath($targetProduct->config_file_path);
                $zip->addFromString($targetProduct->config_file_path, $this->buildFaveoConfig($targetProduct, $version));
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Refuses an absolute path or a `..` traversal segment in a product's
     * declared config_file_path — the one place every write actually
     * happens, and the only guard that covers a direct-DB/tinker write
     * that bypasses admin-form validation entirely.
     */
    private function assertSafeZipEntryPath(string $path): void
    {
        if (str_starts_with($path, '/') || str_contains($path, '..')) {
            throw new RuntimeException("Refusing to stamp an unsafe zip-internal path: {$path}");
        }
    }

    /**
     * Builds the full contents of a product's config file, written
     * wherever that product's own config_file_path points.
     */
    private function buildFaveoConfig(Product $targetProduct, string $version): string
    {
        $lines = [
            'APL_SALT='.$this->getAplSalt($targetProduct),
            'PRODUCT_KEY='.$targetProduct->product_key,
            'APP_NAME='.$targetProduct->name,
            'APP_VERSION='.$version,
            'PRODUCT_ID='.$targetProduct->id,
            'PRODUCT_NAME_FOR_AUTO_UPDATE='.$targetProduct->name,
            'LICENSE_MODE=DATABASE',
            '',
        ];

        return implode("\n", $lines);
    }

    /**
     * Returns $targetProduct's APL_SALT, generating and persisting one
     * the first time it's needed. Must never change for a product once
     * shipped, so this reuses the stored value if there already is one.
     */
    private function getAplSalt(Product $targetProduct): string
    {
        if (! empty($targetProduct->apl_salt)) {
            return $targetProduct->apl_salt;
        }

        $salt = bin2hex(random_bytes(8));
        $targetProduct->update(['apl_salt' => $salt]);

        return $salt;
    }
}
