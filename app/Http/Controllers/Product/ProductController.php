<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\License\Services\ProductBundleStampingService;
use App\Model\Common\StatusSetting;
use App\Model\License\LicenseType;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxProductRelation;
use App\Model\Product\Price;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\Traits\Upload\ChunkUpload;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends BaseProductController
{
    use ChunkUpload;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var Price
     */
    public $price;

    /**
     * @var LicenseType
     */
    public $type;

    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var ProductGroup
     */
    public $group;

    /**
     * @var Plan
     */
    public $plan;

    /**
     * @var Tax
     */
    public $tax;

    /**
     * @var TaxProductRelation
     */
    public $tax_relation;

    /**
     * @var TaxClass
     */
    public $tax_class;

    /**
     * @var ProductUpload
     */
    public $product_upload;

    public function __construct(ProductBundleStampingService $stampingService)
    {
        parent::__construct($stampingService);

        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['adminDownload', 'userDownload']]);

        $product = new Product;
        $this->product = $product;

        $price = new Price;
        $this->price = $price;

        $type = new LicenseType;
        $this->type = $type;

        $subscription = new Subscription;
        $this->subscription = $subscription;

        $currency = new Currency;
        $this->currency = $currency;

        $group = new ProductGroup;
        $this->group = $group;

        $plan = new Plan;
        $this->plan = $plan;

        $tax = new Tax;
        $this->tax = $tax;

        $period = new Period;
        $this->period = $period; // @phpstan-ignore property.notFound

        $tax_relation = new TaxProductRelation;
        $this->tax_relation = $tax_relation;

        $tax_class = new TaxClass;
        $this->tax_class = $tax_class;

        $product_upload = new ProductUpload;
        $this->product_upload = $product_upload;
    }

    public function getProductDropdown(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $productsQuery = Product::where('invoice_hidden', 0)
            ->when($searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            })
            ->paginate($limit, ['*'], 'page', $page);

        $productsQuery->getCollection()->transform(fn ($item): array => ['id' => $item->id, 'name' => $item->name]);

        return successResponse('', $productsQuery);
    }

    public function getProductPlans(Request $request, int $productId): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $limit = $request->input('limit', 10);
        $request->input('page', 1);

        $plans = Plan::select('id', 'name')
            ->where('product', $productId)
            ->when($searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            })
            ->paginate($limit);

        return successResponse('', $plans);
    }

    public function getAllProducts(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = in_array($request->input('sort-order'), ['asc', 'desc']) ? $request->input('sort-order') : 'asc';
        $limit = $request->input('limit', 10);

        $sortFieldMap = [
            'name' => 'products.name',
            'license_type' => 'products.type',
            'group' => 'products.group',
            'created_at' => 'products.created_at',
        ];
        $sortField = $sortFieldMap[$request->input('sort-field')] ?? 'products.created_at';

        $products = Product::select('products.id', 'products.name', 'products.image', 'products.group', 'products.type', 'products.created_at')
            ->with([
                'groupRelation',
                'licenseType',
            ])
            ->when($searchQuery, function ($query, string $searchQuery): void {
                $query->where('products.name', 'like', sprintf('%%%s%%', $searchQuery))
                    ->orWhereHas('groupRelation', function ($q) use ($searchQuery): void {
                        $q->where('name', 'like', sprintf('%%%s%%', $searchQuery));
                    })
                    ->orWhereHas('licenseType', function ($q) use ($searchQuery): void {
                        $q->where('name', 'like', sprintf('%%%s%%', $searchQuery));
                    });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($limit);

        $products->getCollection()->transform(function ($product): array {
            $permissions = LicensePermissionsController::getPermissionsForProduct($product->id);
            $download_url = (empty($permissions['downloadPermission']))
                ? null
                : url('product/download/'.$product->id);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'group' => $product->groupRelation?->name,
                'license_type' => $product->licenseType?->name,
                'action' => [
                    'edit_url' => url('products/'.$product->id.'/edit'),
                    'download_url' => $download_url,
                ],
                'created_at' => $product->created_at,
            ];
        });

        return successResponse('', $products);
    }

    public function deleteBulkProducts(Request $request): JsonResponse
    {
        $ids = $request->input('product_ids', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                $products = Product::whereIn('id', $ids)->get();

                foreach ($products as $product) {
                    $product->delete();
                }
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse(__('message.errors_occurs_delete_product').' '.$exception->getMessage());
        }
    }

    public function getProduct(Request $request, int $productId): JsonResponse
    {
        try {
            $product = Product::with([
                'groupRelation:id,name',
                'licenseType:id,name',
                'taxes',
                'planRelation',
            ])->findOrFail($productId);

            $githubStatus = StatusSetting::value('github_status');

            return successResponse('', [
                'product' => $product,
                'github_status' => (bool) $githubStatus,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function productUploadCreate(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'producttitle' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'filename' => ['required', 'string', 'max:255'],
            'dependencies' => ['required', 'array'],
            'description' => ['required'],
            'release_type' => ['required'],
        ], [
            'producttitle.required' => __('validation.product_validate.producttitle_required'),
            'version.required' => __('validation.product_validate.version_required'),
            'filename.required' => __('validation.product_validate.filename_required'),
            'dependencies.required' => __('validation.product_validate.dependencies_required'),
            'description' => __('validation.product_vaidation.discription_required'),
            'release_type' => __('validation.product_validate.release_type_required'),
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $product): void {
                $this->createProductUpload($product, $validated['producttitle'], $validated['filename'], $validated['version'], $validated, $request->boolean('is_private'), $request->boolean('is_restricted'));
            });

            return successResponse(__('message.product_uploaded_successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

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
     */
    public function applyBuildToProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'dependencies' => ['required', 'array'],
            'description' => ['required'],
            'release_type' => ['required'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['integer', 'exists:products,id'],
            'products.*.version' => ['required', 'string', 'max:50'],
        ], [
            'filename.required' => __('validation.product_validate.filename_required'),
            'dependencies.required' => __('validation.product_validate.dependencies_required'),
            'description' => __('validation.product_vaidation.discription_required'),
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

                    $this->createProductUpload($product, $product->name, $validated['filename'], $version, $validated, $request->boolean('is_private'), $request->boolean('is_restricted'));
                }
            });

            return successResponse(__('message.product_uploaded_successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Shared by productUploadCreate (single product, admin-typed title) and
     * applyBuildToProducts (many products, title defaults to the product's own
     * name) — creates the ProductUpload row and bumps the product's version.
     *
     * @param  array<mixed>  $validated
     */
    private function createProductUpload(Product $product, string $title, string $file, string $version, array $validated, bool $isPrivate, bool $isRestricted): void
    {
        ProductUpload::create([
            'product_id' => $product->id,
            'title' => $title,
            'description' => $validated['description'],
            'version' => $version,
            'file' => $file,
            'is_private' => $isPrivate,
            'is_restricted' => $isRestricted,
            'release_type' => $validated['release_type'],
            'dependencies' => json_encode($validated['dependencies']),
        ]);

        $product->update(['version' => $version]);
    }

    /**
     * Paginated list of a product's version uploads, for the DataTable on the
     * product edit page's "Versions" tab.
     */
    public function getProductUploads(int $productId, Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');
            $search = $request->input('search-query', '');

            $allowed = ['created_at', 'version', 'title', 'release_type', 'status'];
            if (! in_array($sortField, $allowed, strict: true)) {
                $sortField = 'created_at';
            }

            $uploads = ProductUpload::where('product_id', $productId)
                ->when($search, function ($q, $search): void {
                    $q->where(function ($qq) use ($search): void {
                        $qq->where('title', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('version', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('release_type', 'like', sprintf('%%%s%%', $search));
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit, ['*'], 'page', $page);

            $uploads->getCollection()->transform(fn ($u): array => [
                'id' => $u->id,
                'title' => $u->title,
                'description' => $u->description,
                'version' => $u->version,
                'release_type' => $u->release_type,
                'file' => $u->file,
                'status' => $u->status,
                'created_at' => $u->created_at,
            ]);

            return successResponse('', $uploads);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Single version upload, for the edit form.
     */
    public function getProductUpload(int $productUploadId): JsonResponse
    {
        try {
            $u = ProductUpload::findOrFail($productUploadId);

            return successResponse('', [
                'id' => $u->id,
                'product_id' => $u->product_id,
                'title' => $u->title,
                'description' => $u->description,
                'version' => $u->version,
                'file' => $u->file,
                'release_type' => $u->release_type,
                'is_private' => (bool) $u->is_private,
                'is_restricted' => (bool) $u->is_restricted,
                'dependencies' => json_decode((string) $u->getRawOriginal('dependencies'), associative: true) ?: [],
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Update a version's metadata. A replacement file is optional — see the
     * `filename` handling below for how it's routed.
     */
    public function updateProductUpload(int $productUploadId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'dependencies' => ['required', 'array'],
            'release_type' => ['required'],
        ], [
            'title.required' => __('validation.extend_product.title_required'),
            'version.required' => __('validation.extend_product.version_required'),
            'dependencies.required' => __('validation.extend_product.dependencies_required'),
        ]);

        try {
            $upload = ProductUpload::findOrFail($productUploadId);

            $payload = [
                'title' => $validated['title'],
                'description' => $request->input('description'),
                'version' => $validated['version'],
                'dependencies' => json_encode($validated['dependencies']),
                'is_private' => $request->boolean('is_private'),
                'is_restricted' => $request->boolean('is_restricted'),
                'release_type' => $validated['release_type'],
            ];

            if ($request->filled('filename')) {
                $payload['file'] = $request->input('filename');
            }

            $upload->update($payload);

            $productSku = $upload->product->product_sku ?? null;
            if ($productSku) {
                resolve(AutoUpdateController::class)
                    ->editVersion($validated['version'], $productSku);
            }

            return successResponse(__('message.product_updated_successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Deletes one or more version uploads — used by both the single-row
     * delete button and the Versions tab's bulk-select delete
     * (VersionTableActions.vue / ProductEdit.vue's confirmBulkDeleteVersions),
     * which both already send { select: [ids] } to this same endpoint.
     */
    public function deleteBulkProductUploads(Request $request): JsonResponse
    {
        $ids = $request->input('select', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                ProductUpload::whereIn('id', $ids)->get()->each->delete();
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function productCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Scoped to the group, not global — real catalog data has the same
            // product name intentionally reused across different groups (e.g.
            // "Helpdesk Enterprise" under Perpetual/Recurring/Cloud), just never
            // twice within the same group.
            'name' => ['required', Rule::unique('products', 'name')->where('group', $request->input('group'))],
            'type' => ['required'],
            'product_type' => ['required', 'in:independent,addon'],
            'config_file_path' => ['nullable', 'string', 'max:255', 'regex:/^(?!\/)(?!.*\.\.)[^\\\\]+$/'],
            'license_file_path' => ['nullable', 'string', 'max:255', 'regex:/^(?!\/)(?!.*\.\.)[^\\\\]+$/'],
            'description' => ['required'],
            'product_description' => ['required'],
            'image' => ['sometimes', 'mimes:jpeg,png,jpg', 'max:2048'],
            'product_sku' => ['required', 'unique:products,product_sku'],
            'group' => ['required'],
            'show_agent' => ['required'],
        ], [
            'product_sku.unique' => __('validation.product_sku_unique'),
            'name.unique' => __('validation.product_name_unique'),
            'show_agent.required' => __('validation.product_show_agent_required'),
            'config_file_path.regex' => __('validation.config_file_path_regex'),
            'license_file_path.regex' => __('validation.license_file_path_regex'),
        ]);

        // This app doesn't auto-convert empty strings to null (see Kernel.php)
        // — keep both paths as actual NULL rather than '' when left blank, so
        // "don't write one" (see ProductBundleStampingService) behaves correctly.
        $validated['config_file_path'] = empty($validated['config_file_path']) ? null : $validated['config_file_path'];
        $validated['license_file_path'] = empty($validated['license_file_path']) ? null : $validated['license_file_path'];

        try {
            DB::transaction(function () use ($request, $validated): void {
                // Handle Image Upload
                if ($request->hasFile('image')) {
                    $validated['image'] = basename((string) Attach::put('common/images/', $request->file('image'), null, true));
                }

                $validated['show_agent'] = $request->boolean('show_agent');
                $validated['highlight'] = $request->boolean('highlight');
                $validated['add_to_contact'] = $request->boolean('add_to_contact');
                $validated['can_modify_agent'] = $request->boolean('can_modify_agent');
                $validated['can_modify_quantity'] = $request->boolean('can_modify_quantity');
                $validated['require_domain'] = $request->boolean('require_domain');
                $validated['hidden'] = $request->boolean('hidden');
                $validated['invoice_hidden'] = $request->boolean('invoice_hidden');
                $validated['whatsapp_integration'] = $request->boolean('whatsapp_integration');

                // Filter only fillable fields
                $data = array_intersect_key($validated, array_flip((new Product)->getFillable()));

                // Tax status: Taxable (1) / None (0)
                $data['tax_apply'] = $request->boolean('tax_status') ? 1 : 0;

                // Create Product
                $product = Product::create($data);

                // A taxable product carries exactly one tax class.
                if ($data['tax_apply'] === 1 && $request->filled('tax_class_id')) {
                    TaxProductRelation::create([
                        'product_id' => $product->id,
                        'tax_class_id' => $request->input('tax_class_id'),
                    ]);
                }
            });

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateProduct(int $productId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Scoped to the group, not global — see productCreate for why.
            'name' => ['required', Rule::unique('products', 'name')->where('group', $request->input('group'))->ignore($productId)],
            'type' => ['required'],
            'product_type' => ['required', 'in:independent,addon'],
            'config_file_path' => ['nullable', 'string', 'max:255', 'regex:/^(?!\/)(?!.*\.\.)[^\\\\]+$/'],
            'license_file_path' => ['nullable', 'string', 'max:255', 'regex:/^(?!\/)(?!.*\.\.)[^\\\\]+$/'],
            'description' => ['required'],
            'product_description' => ['required'],
            'image' => ['sometimes', 'mimes:jpeg,png,jpg', 'max:2048'],
            'file' => ['sometimes', 'file', 'max:102400'], // NOSONAR — 100 MB limit is intentional for product file downloads
            'product_sku' => ['required', Rule::unique('products', 'product_sku')->ignore($productId)],
            'group' => ['required'],
            'show_agent' => ['required'],
        ], [
            'name.required' => __('validation.product_controller.name_required'),
            'name.unique' => __('validation.product_name_unique'),
            'type.required' => __('validation.product_controller.type_required'),
            'description.required' => __('validation.product_controller.description_required'),
            'product_description.required' => __('validation.product_controller.product_description_required'),
            'image.mimes' => __('validation.product_controller.image_mimes'),
            'image.max' => __('validation.product_controller.image_max'),
            'product_sku.required' => __('validation.product_controller.product_sku_required'),
            'product_sku.unique' => __('validation.product_sku_unique'),
            'group.required' => __('validation.product_controller.group_required'),
            'show_agent.required' => __('validation.product_controller.show_agent_required'),
            'config_file_path.regex' => __('validation.config_file_path_regex'),
            'license_file_path.regex' => __('validation.license_file_path_regex'),
        ]);

        // See productCreate — keep both paths as actual NULL rather than ''
        // when left blank.
        $validated['config_file_path'] = empty($validated['config_file_path']) ? null : $validated['config_file_path'];
        $validated['license_file_path'] = empty($validated['license_file_path']) ? null : $validated['license_file_path'];

        try {
            DB::transaction(function () use ($validated, $request, $productId): void {
                $product = Product::findOrFail($productId);

                // Handle image upload
                if ($request->hasFile('image')) {
                    $validated['image'] = basename((string) Attach::put('common/images/', $request->file('image'), null, true));
                }

                // Cart-related flags
                $validated['show_agent'] = $request->boolean('show_agent');
                $validated['highlight'] = $request->boolean('highlight');
                $validated['add_to_contact'] = $request->boolean('add_to_contact');
                $validated['can_modify_agent'] = $request->boolean('can_modify_agent');
                $validated['can_modify_quantity'] = $request->boolean('can_modify_quantity');
                $validated['require_domain'] = $request->boolean('require_domain');
                $validated['hidden'] = $request->boolean('hidden');
                $validated['invoice_hidden'] = $request->boolean('invoice_hidden');
                $validated['whatsapp_integration'] = $request->boolean('whatsapp_integration');

                // Update product with only fillable fields
                $fillableData = array_intersect_key($validated, array_flip($product->getFillable()));
                $fillableData['tax_apply'] = $request->boolean('tax_status') ? 1 : 0;
                $product->update($fillableData);

                // Reset to a single tax class (or none when not taxable).
                TaxProductRelation::where('product_id', $product->id)->delete();
                if ($fillableData['tax_apply'] === 1 && $request->filled('tax_class_id')) {
                    TaxProductRelation::create([
                        'product_id' => $product->id,
                        'tax_class_id' => $request->input('tax_class_id'),
                    ]);
                }

                // Update version from GitHub if provided
                if ($request->filled('github_owner') && $request->filled('github_repository')) {
                    $this->updateVersionFromGithub(
                        $product->id,
                        $request->input('github_owner'),
                        $request->input('github_repository')
                    );
                }
            });

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
