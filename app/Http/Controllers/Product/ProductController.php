<?php

namespace App\Http\Controllers\Product;

// use Illuminate\Http\Request;
use App\Facades\Attach;
use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Common\Setting;
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
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lang;
use Logger;
use Validator;

// use Input;

class ProductController extends BaseProductController
{
    use ChunkUpload;

    /**
     * @var \App\Model\Product\Product
     */
    public $product;

    /**
     * @var \App\Model\Product\Price
     */
    public $price;

    /**
     * @var \App\Model\License\LicenseType
     */
    public $type;

    /**
     * @var \App\Model\Product\Subscription
     */
    public $subscription;

    /**
     * @var \App\Model\Payment\Currency
     */
    public $currency;

    /**
     * @var \App\Model\Product\ProductGroup
     */
    public $group;

    /**
     * @var \App\Model\Payment\Plan
     */
    public $plan;

    /**
     * @var \App\Model\Payment\Tax
     */
    public $tax;

    /**
     * @var \App\Model\Payment\TaxProductRelation
     */
    public $tax_relation;

    /**
     * @var \App\Model\Payment\TaxClass
     */
    public $tax_class;

    /**
     * @var \App\Model\Product\ProductUpload
     */
    public $product_upload;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['adminDownload', 'userDownload']]);

        $product = new Product();
        $this->product = $product;

        $price = new Price();
        $this->price = $price;

        $type = new LicenseType();
        $this->type = $type;

        $subscription = new Subscription();
        $this->subscription = $subscription;

        $currency = new Currency();
        $this->currency = $currency;

        $group = new ProductGroup();
        $this->group = $group;

        $plan = new Plan();
        $this->plan = $plan;

        $tax = new Tax();
        $this->tax = $tax;

        $period = new Period();
        $this->period = $period;

        $tax_relation = new TaxProductRelation();
        $this->tax_relation = $tax_relation;

        $tax_class = new TaxClass();
        $this->tax_class = $tax_class;

        $product_upload = new ProductUpload();
        $this->product_upload = $product_upload;
    }

    // Save file Info in Modal popup
    public function save(Request $request)
    {
        $this->validate(
            $request,
            [
                'producttitle' => 'required',
                'version' => 'required',
                'filename' => 'required',
                'dependencies' => 'required',
            ],
            [
                'version.required' => __('validation.product_validate.version_required'),
                'filename.required' => __('validation.product_validate.filename_required'),
                'dependencies.required' => __('validation.product_validate.dependencies_required'),

            ]
        );

        try {
            $product_id = Product::find($request->input('product_id'));

            $this->product_upload->product_id = $product_id->id;
            $this->product_upload->title = $request->input('producttitle');
            $this->product_upload->description = $request->input('description');
            $this->product_upload->version = $request->input('version');
            $this->product_upload->file = $request->input('filename');

            $this->product_upload->is_private = $request->input('is_private');
            $this->product_upload->release_type = $request->input('release_type');
            $this->product_upload->is_restricted = $request->input('is_restricted');
            $this->product_upload->dependencies = json_encode($request->input('dependencies'));

            $this->product_upload->save();

            $this->product->where('id', $product_id->id)->update(['version' => $request->input('version')]);

            return successResponse(__('message.product_uploaded_successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function fileDestroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            $storagePath = Setting::find(1)->value('file_storage');
            if (empty($ids)) {
                return successResponse(__('message.select-a-row'));
            }

            foreach ($ids as $id) {
                $product = $this->product_upload->find($id);
                if ($product) {
                    $filePath = $storagePath.'/'.$product->file;
                    if (Attach::exists($filePath)) {
                        Attach::delete($filePath);
                    }

                    $product->delete();
                }
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse(__('message.errors_occurs_delete_product').$exception->getMessage());
        }
    }

    public function getSubscriptionCheckScript(): void
    {
        url('get-price');
    }

    public function uploadImage(Request $request)
    {
        try {
            $setting = Setting::find(1);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('public/uploads/tinymce', $filename);
            }

            if ($request->input('url')) {
                $url = $request->input('url');
                $client = new Client();
                $response = $client->get($url, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0', // Some servers require User-Agent
                    ],
                ]);
                $contents = $response->getBody()->getContents();

                $ext = pathinfo(parse_url((string) $url, PHP_URL_PATH), PATHINFO_EXTENSION);
                $filename = 'tinymce/'.uniqid().'.'.($ext ?: 'jpg');
                Storage::put('public/uploads/'.$filename, $contents);
                $path = Storage::url('public/uploads/'.$filename);
            }

            return response()->json([
                'location' => asset(str_replace('public/', 'storage/', $path)),
            ]);
        } catch (Exception) {
            return response()->json(['error' => 'No file uploaded.'], 500);
        }
    }

    public function getProductDropdown(Request $request)
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

    public function getProductPlans(Request $request, $productId)
    {
        $searchQuery = $request->input('search-query', '');
        $limit = $request->input('limit', 10);
        $request->input('page', 1);

        $plans = Plan::select('id', 'name')
            ->where('product', $productId)
            ->when($searchQuery, function ($query, string $searchQuery): void {
                $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
            })
            ->simplePaginate($limit);

        return successResponse('', $plans);
    }

    public function getAllProducts(Request $request)
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
                      });
            })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        $products->getCollection()->transform(function ($product): array {
            $permissions = LicensePermissionsController::getPermissionsForProduct($product->id);
            $download_url = (is_array($permissions) && ! empty($permissions['downloadPermission']))
                ? url('product/download/' . $product->id)
                : null;

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

    public function deleteBulkProducts(Request $request)
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

    public function getProduct(Request $request, $productId)
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

    public function productUploadCreate(Request $request, $productId)
    {
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
            $product = Product::findOrFail($productId);

            DB::transaction(function () use ($request, $validated, $product): void {
                // Save the product upload
                $productUpload = ProductUpload::create([
                    'product_id' => $product->id,
                    'title' => $validated['producttitle'],
                    'description' => $validated['description'],
                    'version' => $validated['version'],
                    'file' => $validated['filename'],
                    'is_private' => $request->boolean('is_private'),
                    'is_restricted' => $request->boolean('is_restricted'),
                    'release_type' => $validated['release_type'],
                    'dependencies' => json_encode($validated['dependencies']),
                ]);

                // Update the product version
                $product->update(['version' => $validated['version']]);
            });

            return successResponse(__('message.product_uploaded_successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Paginated list of a product's version uploads, for the DataTable on the
     * product edit page's "Versions" tab.
     */
    public function getProductUploads($productId, Request $request)
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
    public function getProductUpload($productUploadId)
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
     * Update a version's metadata (the file itself is not changed on edit).
     */
    public function updateProductUpload($productUploadId, Request $request)
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

            // Only replace the file when a new one was uploaded.
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

    public function productCreate(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'unique:products,name'],
            'type' => ['required'],
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
        ]);

        try {
            DB::transaction(function () use ($request, $validated): void {
                // Handle Image Upload
                if ($request->hasFile('image')) {
                    $validated['image'] = basename(Attach::put('common/images/', $request->file('image'), null, true));
                }

                $validated['show_agent'] = $request->boolean('show_agent');
                $validated['highlight'] = $request->boolean('highlight');
                $validated['add_to_contact'] = $request->boolean('add_to_contact');
                $validated['can_modify_agent'] = $request->boolean('can_modify_agent');
                $validated['can_modify_quantity'] = $request->boolean('can_modify_quantity');

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

    public function updateProduct($productId, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'type' => ['required'],
            'description' => ['required'],
            'product_description' => ['required'],
            'image' => ['sometimes', 'mimes:jpeg,png,jpg', 'max:2048'],
            'file' => ['sometimes', 'file'],
            'product_sku' => ['required'],
            'group' => ['required'],
            'show_agent' => ['required'],
        ], [
            'name.required' => __('validation.product_controller.name_required'),
            'type.required' => __('validation.product_controller.type_required'),
            'description.required' => __('validation.product_controller.description_required'),
            'product_description.required' => __('validation.product_controller.product_description_required'),
            'image.mimes' => __('validation.product_controller.image_mimes'),
            'image.max' => __('validation.product_controller.image_max'),
            'product_sku.required' => __('validation.product_controller.product_sku_required'),
            'group.required' => __('validation.product_controller.group_required'),
            'show_agent.required' => __('validation.product_controller.show_agent_required'),
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $productId): void {
                $product = Product::findOrFail($productId);

                // Handle image upload
                if ($request->hasFile('image')) {
                    $validated['image'] = basename(Attach::put('common/images/', $request->file('image'), null, true));
                }

                // Cart-related flags
                $validated['show_agent'] = $request->boolean('show_agent');
                $validated['highlight'] = $request->boolean('highlight');
                $validated['add_to_contact'] = $request->boolean('add_to_contact');
                $validated['can_modify_agent'] = $request->boolean('can_modify_agent');
                $validated['can_modify_quantity'] = $request->boolean('can_modify_quantity');

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
