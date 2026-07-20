<?php

namespace App\License\Services;

use App\Facades\Attach;
use App\Model\Configure\ProductPluginGroup;
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
 * downloadable file, by stamping that product's identity into it:
 * storage/faveoconfig.ini for a core product, or config.php for a
 * standalone plugin. Runs on every download of every product — there is
 * no "already correct, serve as-is" shortcut.
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
        if (empty($targetProduct->product_key)) {
            // Nothing downstream (AFU download/update, AFL license verify) can
            // ever authenticate a build stamped with no key — refuse rather
            // than silently ship an artifact nobody could ever use.
            throw new RuntimeException("Product #{$targetProduct->id} ({$targetProduct->name}) has no product_key set — refusing to stamp a build for it.");
        }

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

        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);
        $safeVersion = preg_replace('/[^A-Za-z0-9.]+/', '-', $version->version);
        $downloadName = Str::slug($product->name).'-'.$safeVersion.'.'.$extension;

        return response()->download($localStampedPath, $downloadName)->deleteFileAfterSend();
    }

    /**
     * Validates a just-uploaded build's layout before it's accepted:
     * storage/ (core product) or config.php (standalone plugin) must sit
     * at the zip's true root, not nested inside an extra wrapper folder
     * (e.g. GitHub's "Download ZIP" wraps everything in "repo-branch/").
     * A wrapped zip would silently break every path this service
     * reads/writes.
     *
     * @return string|null null when the structure is fine; otherwise an
     *                     admin-facing message describing what's wrong.
     */
    public function validateBuildStructure(string $localZipPath): ?string
    {
        $zip = new ZipArchive;

        if ($zip->open($localZipPath) !== true) {
            return __('message.zip_unreadable');
        }

        try {
            $topLevelNames = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);

                if ($entryName === false) {
                    continue;
                }

                if (str_starts_with($entryName, 'storage/') || $entryName === 'config.php') {
                    return null;
                }

                if (preg_match('#^([^/]+)/#', $entryName, $matches)) {
                    $topLevelNames[$matches[1]] = true;
                }
            }

            if (count($topLevelNames) === 1) {
                return __('message.zip_wrapper_folder_detected', ['folder' => (string) array_key_first($topLevelNames)]);
            }

            return __('message.zip_missing_build_root');
        } finally {
            $zip->close();
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
     * Does the actual in-place stamping of the local zip copy. Branches
     * on the zip's shape:
     * - Root-level config.php present → this is a standalone plugin zip
     *   (mirrors core/plugins.py's output). Only config.php is patched
     *   plus, for a File-mode order, the signed license file — the zip's
     *   own root is where this ends up once extracted into an existing
     *   core install's app/Plugins/<name>/, matching where favMer looks
     *   for that plugin's license.json. storage/faveoconfig.ini and
     *   plugin-folder filtering are core-product-only concepts and don't
     *   apply here.
     * - Otherwise → this is a core product zip. Rewrites the app config
     *   file, filters app/Plugins/* down to what's actually bundled, and
     *   (for a File-mode order) embeds the signed license file.
     */
    private function stampZip(string $localZipPath, Product $targetProduct, string $version, ?Order $order = null): void
    {
        $zip = new ZipArchive;

        if ($zip->open($localZipPath) !== true) {
            throw new RuntimeException("Unable to open canonical build for stamping: {$localZipPath}");
        }

        try {
            $rootConfig = $this->readEntry($zip, 'config.php');

            if ($rootConfig !== null) {
                $zip->addFromString('config.php', $this->patchPluginConfig($rootConfig, $targetProduct, $version));
            } else {
                $configEntryName = $this->findFaveoConfigEntryName($zip) ?? 'storage/faveoconfig.ini';
                $zip->addFromString($configEntryName, $this->buildFaveoConfig($targetProduct, $version, $order));

                $this->filterBundledPluginFolders($zip, $targetProduct, $version, $order);
            }

            // Same call regardless of zip shape: addLicenseFileEntry writes to
            // the zip's own root either way, which is exactly right for both
            // — the true root of a core product zip, or (once extracted into
            // an existing install's app/Plugins/<name>/) the plugin's own root.
            if ($this->isFileMode($order)) {
                $this->addLicenseFileEntry($zip, $targetProduct, $order);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Keeps only the app/Plugins/<folder>/ entries for plugins actually
     * configured as bundled for this product (product_plugin_group),
     * stamping each kept folder's config.php with that plugin's own
     * identity. Every unmatched folder is stripped. A product with no
     * bundled plugins ends up with app/Plugins/ empty.
     *
     * Folders are matched to a plugin by their own folder name (e.g.
     * 'AdHocApproval'), compared against that plugin's own `slug` column
     * — the folder name already *is* the plugin's path, since that's
     * exactly what it was written as (app/Plugins/<path>/), so there's no
     * need to open config.php just to confirm it. Not matched by
     * product_id: real plugin config.php files write product_id as a
     * bare, unquoted integer rather than a quoted string, which makes it
     * unreliable to pull back out of PHP source text. Not matched by
     * display name either, since a folder name and a product's display
     * name aren't guaranteed to match — name is only used as a fallback,
     * for a plugin that has no slug set yet.
     *
     * When $order is set and in File mode, also writes each kept plugin's
     * own signed license.json into its folder — favMer verifies a bundled
     * plugin's license against app/Plugins/<folder>/public/script/signature/
     * license.json, a separate file from the core product's own.
     */
    private function filterBundledPluginFolders(ZipArchive $zip, Product $targetProduct, string $version, ?Order $order = null): void
    {
        $bundledPlugins = $targetProduct->productPluginGroupsAsProduct()
            ->with('plugin:id,name,slug,product_key')
            ->get()
            ->map(fn (ProductPluginGroup $row): Product => $row->plugin)
            ->filter();

        $bundledBySlug = $bundledPlugins->filter(fn (Product $plugin): bool => ! empty($plugin->slug))
            ->keyBy(fn (Product $plugin): string => (string) $plugin->slug);
        $bundledByNormalizedName = $bundledPlugins->keyBy(fn (Product $plugin): string => $this->normalizeForMatch($plugin->name));

        $entriesByFolder = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if ($entryName !== false && preg_match('#^app/Plugins/([^/]+)/#', $entryName, $matches)) {
                $entriesByFolder[$matches[1]][] = $entryName;
            }
        }

        $entriesToDelete = [];
        $foldersToStamp = [];

        foreach ($entriesByFolder as $folderName => $entries) {
            // The folder name already is the plugin's path/slug — that's
            // exactly what it was written as (app/Plugins/<path>/) — so no
            // file needs to be opened just to confirm that. config.php is
            // still read here, but only so it's ready to patch below.
            $configContent = $this->readEntry($zip, "app/Plugins/{$folderName}/config.php");

            $matchedPlugin = $bundledBySlug->get($folderName)
                ?? $bundledByNormalizedName->get($this->normalizeForMatch($folderName));

            if (! $matchedPlugin) {
                array_push($entriesToDelete, ...$entries);

                continue;
            }

            if ($configContent !== null) {
                $foldersToStamp[] = [$folderName, $matchedPlugin, $configContent];
            }
        }

        foreach ($entriesToDelete as $entryName) {
            $zip->deleteName($entryName);
        }

        foreach ($foldersToStamp as [$folderName, $plugin, $configContent]) {
            $zip->addFromString("app/Plugins/{$folderName}/config.php", $this->patchPluginConfig($configContent, $plugin, $version));

            if ($this->isFileMode($order)) {
                $this->addLicenseFileEntry($zip, $plugin, $order, "app/Plugins/{$folderName}/");
            }
        }
    }

    /**
     * Reads one entry's contents out of an open zip.
     *
     * @return string|null null if the entry doesn't exist or can't be read.
     */
    private function readEntry(ZipArchive $zip, string $path): ?string
    {
        $index = $zip->locateName($path);

        if ($index === false) {
            return null;
        }

        $content = $zip->getFromIndex($index);

        return $content === false ? null : $content;
    }

    /**
     * Lowercases and strips non-alphanumeric characters, for comparing a
     * plugin's display name against a zip folder name.
     */
    private function normalizeForMatch(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $value) ?? $value);
    }

    /**
     * Patches product_id/product_key/version in place inside a plugin's
     * config.php source (a plain PHP array return statement). Every
     * other field (name, path, description, author, website, settings,
     * product_display_name) is left untouched — billing has no source
     * of truth for those.
     */
    private function patchPluginConfig(string $existingContent, Product $targetProduct, string $version): string
    {
        $content = $existingContent;

        // product_id ships as a bare, unquoted integer in real plugin
        // config.php files — match either that or a quoted-string form on
        // the way in (so older/differently-generated files still patch),
        // but always write the bare-integer form back out, matching the
        // convention the shipped product actually uses.
        $content = preg_replace(
            "/'product_id'\s*=>\s*(?:'[^']*'|\d+)/",
            "'product_id' => ".(int) $targetProduct->id,
            $content
        ) ?? $content;

        foreach (['product_key' => $targetProduct->product_key, 'version' => $version] as $key => $value) {
            $content = preg_replace_callback(
                "/'{$key}'\s*=>\s*'[^']*'/",
                fn () => "'{$key}' => '".addslashes((string) $value)."'",
                $content
            ) ?? $content;
        }

        return $content;
    }

    /**
     * Builds the full contents of a core product's app config file
     * (APL_SALT, PRODUCT_KEY, APP_NAME, APP_VERSION, PRODUCT_ID,
     * PRODUCT_NAME_FOR_AUTO_UPDATE, LICENSE_MODE).
     *
     * When $order is set and in File mode, also switches LICENSE_MODE to
     * FILE and adds the signing public key, so the installed product can
     * verify the license file (see addLicenseFileEntry) offline.
     * Otherwise LICENSE_MODE is DATABASE and no key is added.
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
        ];

        if ($isFileMode) {
            $lines[] = 'ED25519_PUBLIC_KEY='.$this->signingService->publicKey();
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Adds $product's signed license file into the zip at the path the
     * installed product reads it from (<$pathPrefix>public/script/signature/
     * license.json), so it can verify itself offline against the public key
     * already written into the core product's app config. Does nothing if
     * there's no license record to build one from — e.g. a bundled plugin
     * that isn't actually attached to this order's license.
     *
     * $pathPrefix is empty for the core product (public/script/signature/...
     * at the zip root) and "app/Plugins/<folder>/" for a bundled plugin,
     * matching where favMer looks up each one's own license.json.
     */
    private function addLicenseFileEntry(ZipArchive $zip, Product $product, Order $order, string $pathPrefix = ''): void
    {
        $licenseFile = $this->licenseFileService->buildForOrder($order, $product);

        if ($licenseFile !== null) {
            $zip->addFromString($pathPrefix.'public/script/signature/license.json', $licenseFile);
        }
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

    /**
     * Finds the core product's app config file by extension rather than
     * assuming it's always named "faveoconfig.ini" — any single .ini
     * file directly under storage/ is treated as the one to overwrite.
     *
     * @return string|null null if no .ini file exists there yet (a
     *                     brand new build).
     */
    private function findFaveoConfigEntryName(ZipArchive $zip): ?string
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if ($entryName !== false && preg_match('#^storage/[^/]+\.ini$#', $entryName)) {
                return $entryName;
            }
        }

        return null;
    }
}
