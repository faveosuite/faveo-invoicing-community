<?php

namespace App\License\Services;

use App\Facades\Attach;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use ZipArchive;

/**
 * Turns one uploaded canonical build into a specific product's own
 * downloadable file, by writing that product's identity/license content at
 * whatever destination path *that product itself* declares
 * (Product::config_file_path / license_file_path) — never guessed from the
 * zip's shape, since this also distributes third-party products whose
 * internal layout billing doesn't control. Runs on every download of every
 * product — there is no "already correct, serve as-is" shortcut.
 *
 * Nothing is ever removed from the zip: every bundled plugin ships to every
 * download regardless of what that customer bought, same as any open-source
 * distribution. Licensing is enforced by the product/license check on
 * download, not by withholding code.
 */
class ProductBundleStampingService
{
    public function __construct(
        protected Ed25519SigningService $signingService,
        protected LicenseFileService $licenseFileService,
    ) {
    }

    /**
     * Stamps $targetProduct's identity into a local temp copy of the
     * canonical build and returns that copy's path. Nothing is persisted
     * back to storage — the caller owns the returned file and must delete
     * it once done.
     *
     * Pass $order when the download belongs to a specific customer's
     * order. If that order has File-mode (localized) licensing enabled,
     * a signed license file and the signing public key are embedded too.
     *
     * @throws RuntimeException if the product has no product_key, or the
     *                          canonical file can't be found/opened.
     */
    public function stampToLocalFile(string $canonicalFilePath, Product $targetProduct, string $version, ?Order $order = null): string
    {
        $this->assertHasProductKey($targetProduct);

        $localCopy = $this->copyToLocalTemp($canonicalFilePath);

        try {
            $this->stampZip($localCopy, $targetProduct, $version, $order);
        } catch (Throwable $e) {
            // Don't leave a half-stamped (or never-stamped) copy behind in the
            // system temp dir — this method is called on every download, so a
            // recurring failure here would otherwise accumulate real disk usage.
            @unlink($localCopy);

            throw $e;
        }

        return $localCopy;
    }

    /**
     * Stamps a copy of $storagePath for $product and returns it as a
     * ready-to-return file-download Response (cleans up the temp file
     * after sending). This is the single entry point every download
     * route calls — AFU auto-update, My Orders, admin panel, Deploy
     * wizard — none of them need to know how the file was produced.
     *
     * @throws RuntimeException if stamping fails; callers decide what
     *                          that looks like in their own response.
     */
    public function downloadResponseFor(ProductUpload $version, Product $product, string $storagePath, ?Order $order = null): Response
    {
        $localStampedPath = $this->stampToLocalFile($storagePath, $product, $version->version, $order);
        $sha256 = (string) hash_file('sha256', $localStampedPath);

        $response = response()->download($localStampedPath, $this->downloadName($product, $version->version, pathinfo($storagePath, PATHINFO_EXTENSION)))->deleteFileAfterSend();

        // faveo/license-client refuses an update archive unless these match: Ed25519 over "product_id|version|sha256".
        $response->headers->set('archive_sha256', $sha256);
        $response->headers->set('archive_signature', $this->signingService->sign($product->id.'|'.$version->version.'|'.$sha256));

        return $response;
    }

    /**
     * Same as downloadResponseFor(), for a GitHub zipball already fetched to
     * $localZipPath (this method takes ownership and deletes it). GitHub wraps
     * everything in one `owner-repo-sha/` folder, so the product's paths are
     * written under that folder. A product with neither path set is served
     * as-is — GitHub products never needed a product_key before, and one that
     * doesn't ask for a config/license file must keep downloading.
     */
    public function githubDownloadResponse(string $localZipPath, Product $product, string $tag, ?Order $order = null): Response
    {
        if (! empty($product->config_file_path) || ! empty($product->license_file_path)) {
            try {
                $this->assertHasProductKey($product);
                $this->stampZip($localZipPath, $product, $tag, $order, underRootFolder: true);
            } catch (Throwable $e) {
                @unlink($localZipPath);

                throw $e;
            }
        }

        return response()->download($localZipPath, $this->downloadName($product, $tag, 'zip'))->deleteFileAfterSend();
    }

    private function downloadName(Product $product, string $version, string $extension): string
    {
        return Str::slug($product->name).'-'.preg_replace('/[^A-Za-z0-9.]+/', '-', $version).'.'.$extension;
    }

    /**
     * Nothing downstream (AFU download/update, AFL license verify) can ever
     * authenticate a build stamped with no key — refuse rather than silently
     * ship an artifact nobody could ever use.
     */
    private function assertHasProductKey(Product $product): void
    {
        if (empty($product->product_key)) {
            throw new RuntimeException("Product #{$product->id} ({$product->name}) has no product_key set — refusing to stamp a build for it.");
        }
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
     * Does the actual in-place stamping of the local zip copy: writes
     * $targetProduct's own identity/config content and signed license file
     * at whatever paths *it* declares (config_file_path / license_file_path
     * — either can be blank, meaning "don't write one"). Everything else in
     * the zip, including every bundled plugin folder, is left untouched.
     */
    private function stampZip(string $localZipPath, Product $targetProduct, string $version, ?Order $order = null, bool $underRootFolder = false): void
    {
        $zip = new ZipArchive;

        if ($zip->open($localZipPath) !== true) {
            throw new RuntimeException("Unable to open canonical build for stamping: {$localZipPath}");
        }

        try {
            $this->writeProductFiles($zip, $targetProduct, $version, $order, $underRootFolder ? $this->rootFolder($zip) : '');
        } finally {
            $zip->close();
        }
    }

    /**
     * Writes $product's own config/license content into $zip at whatever
     * path *that product* declares. Either path being blank just skips that
     * write — there's no fallback location to guess.
     */
    private function writeProductFiles(ZipArchive $zip, Product $product, string $version, ?Order $order, string $prefix = ''): void
    {
        if (! empty($product->config_file_path)) {
            $this->assertSafeZipEntryPath($product->config_file_path);
            $zip->addFromString($prefix.$product->config_file_path, $this->buildFaveoConfig($product, $version, $order));
        }

        if ($this->isFileMode($order) && ! empty($product->license_file_path)) {
            $this->assertSafeZipEntryPath($product->license_file_path);
            $licenseFile = $this->licenseFileService->buildForOrder($order, $product);

            if ($licenseFile !== null) {
                $zip->addFromString($prefix.$product->license_file_path, $licenseFile);
            }
        }
    }

    /**
     * The single top-level folder a GitHub zipball wraps everything in
     * (`owner-repo-sha/`, always the first entry), with its trailing slash.
     */
    private function rootFolder(ZipArchive $zip): string
    {
        $first = (string) $zip->getNameIndex(0);
        $slash = strpos($first, '/');

        if ($slash === false) {
            throw new RuntimeException('GitHub zipball has no top-level folder to stamp into.');
        }

        return substr($first, 0, $slash + 1);
    }

    /**
     * Refuses an absolute path or a `..` traversal segment in a product's
     * declared config_file_path/license_file_path — form validation
     * (ProductController) already rejects both, but this is the one place
     * every write actually happens, and it's the only guard that covers the
     * 179 rows backfilled directly against the DB plus any future
     * tinker/direct-DB write that bypasses the controller entirely.
     */
    private function assertSafeZipEntryPath(string $path): void
    {
        if (str_starts_with($path, '/') || str_contains($path, '..')) {
            throw new RuntimeException("Refusing to stamp an unsafe zip-internal path: {$path}");
        }
    }

    /**
     * Builds the full contents of a product's config file (APL_SALT,
     * PRODUCT_KEY, APP_NAME, APP_VERSION, PRODUCT_ID,
     * PRODUCT_NAME_FOR_AUTO_UPDATE, LICENSE_MODE, ED25519_PUBLIC_KEY, SERVER_URL) —
     * written wherever that product's own config_file_path points.
     *
     * When $order is set and in File mode, LICENSE_MODE is FILE so the
     * installed product verifies the license file (see writeProductFiles)
     * offline; otherwise DATABASE. The public key is always added: it also
     * verifies update archive signatures (see downloadResponseFor).
     */
    private function buildFaveoConfig(Product $targetProduct, string $version, ?Order $order = null): string
    {
        $isFileMode = $this->isFileMode($order);

        $lines = [
            'APL_SALT='.$this->getAplSalt($targetProduct),
            'PRODUCT_KEY='.$targetProduct->product_key,
            'APP_NAME='.$targetProduct->name,
            'APP_VERSION='.$version,
            'PRODUCT_ID='.$targetProduct->id,
            'PRODUCT_NAME_FOR_AUTO_UPDATE='.$targetProduct->name,
            'LICENSE_MODE='.($isFileMode ? 'FILE' : 'DATABASE'),
            'ED25519_PUBLIC_KEY='.$this->signingService->publicKey(),
            // The billing this build came from, so the installed product calls it back for licence checks and updates.
            'SERVER_URL='.rtrim((string) config('app.url'), '/'),
            '',
        ];

        return implode("\n", $lines);
    }

    /**
     * Returns $targetProduct's APL_SALT, generating and persisting one
     * the first time it's needed.
     *
     * APL_SALT is real encryption key material the installed product
     * uses to protect a local anti-tamper cache — it must never change
     * for a product once shipped, or every existing install's cached
     * data becomes undecryptable. Reuses the stored value if the product
     * already has one (e.g. a real tier backfilled with its historical
     * value); otherwise generates one now and saves it so every future
     * stamp of this product reuses the same value forever.
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

    /**
     * Whether $order calls for File-mode (localized/offline) licensing to
     * be embedded — the signed license file, the signing public key, and
     * LICENSE_MODE=FILE instead of DATABASE. Single source of truth for
     * that check, reused everywhere it matters.
     *
     * @phpstan-assert-if-true !null $order
     */
    private function isFileMode(?Order $order): bool
    {
        return $order !== null && $order->license_mode === 'File';
    }
}
