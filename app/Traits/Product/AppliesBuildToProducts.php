<?php

namespace App\Traits\Product;

use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait AppliesBuildToProducts
{
    /**
     * Applies one already-uploaded canonical build (chunk-uploaded via
     * `chunkupload`, same as a single-product upload) to many products at
     * once: each target just gets its own ProductUpload row pointing at that
     * same canonical file — replacing what would otherwise be one manual
     * productUploadCreate submission per product. No per-product file is
     * created here: the build is stamped with each product's own identity
     * fresh, on demand, the moment it's actually downloaded — see
     * ProductBundleStampingService and DownloadFileController::downloadFile.
     *
     * Each product carries its own `version` (not one shared value) — tier
     * variants of the same core build typically share a version, but a
     * product like a plugin can be released on its own independent cadence.
     *
     * Shared by the admin screen (ProductController, session + admin auth) and
     * the third-party release API (ThirdPartyApiController, HMAC-signed), which
     * send the same payload and differ only in how they authenticate.
     */
    public function applyBuildToProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            // 'present' not 'required': an empty [] is a legitimate "no
            // dependencies" — 'required' rejects empty arrays outright
            // (Laravel treats count() < 1 as "missing"), which is exactly
            // what the frontend sends by default.
            'dependencies' => ['present', 'array'],
            'description' => ['required'],
            'release_type' => ['required'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.version' => ['required', 'string', 'max:50'],
        ], [
            'filename.required' => __('validation.product_validate.filename_required'),
            'dependencies.present' => __('validation.product_validate.dependencies_required'),
            'description' => __('validation.product_validate.description_required'),
            'release_type' => __('validation.product_validate.release_type_required'),
            'products.required' => __('message.select-a-row'),
            'products.*.version.required' => __('validation.product_validate.version_required'),
        ]);

        $versionsById = [];
        foreach ($validated['products'] as $entry) {
            $versionsById[(int) $entry['id']] = (string) $entry['version'];
        }

        $products = Product::whereIn('id', array_keys($versionsById))->get();

        try {
            DB::transaction(function () use ($validated, $request, $products, $versionsById): void {
                foreach ($products as $product) {
                    $version = $versionsById[$product->id];

                    ProductUpload::createRelease($product, $product->name, $validated['filename'], $version, $validated, $request->boolean('is_private'), $request->boolean('is_restricted'));
                }
            });

            return successResponse(__('message.product_uploaded_successfully'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }
}
