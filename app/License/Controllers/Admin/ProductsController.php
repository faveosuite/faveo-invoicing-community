<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Model\Product\Product;
use App\License\Controllers\Update\AfuProductsController;
use App\License\Models\InstallationLog;
use App\License\Models\License as ApiKeys;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Consist of functionalities for the Product page in Auto Faveo licenser
 * Class ProductsController.
 */
class ProductsController extends Controller
{
    /**
     * stores the product details into the database.
     *
     * @param  ProductRequest  $request
     * @param  $api_key_secret
     * @param  $name
     * @param  $product_sku
     * @param  $status
     * @param  $product_description
     * @param  $product_url_homepage
     * @param  $product_url_download
     * @param  $product_version
     * @param  $product_envato_id
     * @return response that a product details is added with a success response
     */
    public function productAdd(ProductRequest $request)
    {
        $api_action_success = 0;
        $api_error_detected = 0;
        $added_records = 0;

        $api_key_secret = $request->get('api_key_secret');
        $name = $request->get('name');
        $product_sku = $request->get('product_sku');
        $status = $request->get('status');
        $product_description = $request->get('product_description');
        $product_url_homepage = $request->get('product_url_homepage');
        $product_url_download = $request->get('product_url_download');
        $product_version = $request->get('product_version');
        $product_envato_id = $request->get('product_envato_id');

        if (null !== request()->server('REMOTE_ADDR')) {
            $ip_address = request()->server('REMOTE_ADDR');
        } else {
            $ip_address = $request->ip();
        }
        if (! empty($api_key_secret)) {
            // $api = ApiKeys::where('api_key_secret', $api_key_secret)->where('api_key_status', 1)->get()->toArray();

            if (empty($api)) {
                return errorResponse(Lang::get('lang.invalid_api_key'), 404);
            } else {
                $api_ip = new ApiKeys();
                $api_ips = $api_ip->value('api_key_ip');

                if (! empty($api_ips)) {
                    if (! $api_ips->contains($ip_address)) {
                        $api_error_detected = 1;

                        return errorResponse(Lang::get('lang.Api_Acess_not_allowed'), 400);
                    } else {
                        $api_action_success = 1;
                    }
                } else {
                    $api_action_success = 1;
                }
            }

            if (! empty($name) && ! empty($product_sku) && aflValidateIntegerValue($status, 0, 2) && $api_action_success == 1) {
                if (! empty($product_url_homepage) && ! filter_var($product_url_homepage, FILTER_VALIDATE_URL)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.error_producturl'), 400);
                }

                if (! empty($product_envato_id) && ! aflValidateIntegerValue($product_envato_id)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.error_product_envato'), 400);
                }

                if ($api_error_detected != 1) {
                    if (! aflValidateIntegerValue($product_envato_id)) {
                        $product_envato_id = null;
                    }

                    $product_date = date('Y-m-d');

                    //$added_records=doMysqlQuery("INSERT IGNORE INTO apl_products (name, product_description, product_sku, product_url_homepage, product_url_download, product_date, product_version, product_envato_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array($name, $product_description, $product_sku, $product_url_homepage, $product_url_download, $product_date, $product_version, $product_envato_id, $status), array("s", "s", "s", "s", "s", "s", "s", "i", "i"));

                    try {
                        $in = DB::table('afl_products')->insertOrIgnore([
                            'name' => $name,
                            'product_description' => $product_description,
                            'product_sku' => $product_sku,
                            'product_url_homepage' => $product_url_homepage,
                            'product_url_download' => $product_url_download,
                            'product_date' => $product_date,
                            'product_version' => $product_version,
                            'product_envato_id' => $product_envato_id,
                            'status' => $status,
                        ]);
                        $added_records += 1;
                    } catch (Exception $e) {
                        $added_records += 0;
                    }
                    if (! aflValidateIntegerValue($added_records)) {
                        $api_error_detected = 1;

                        return errorResponse(Lang::get('lang.invalid'), 400);
                    } else {
                        $api_action_success = 1;

                        return successResponse(Lang::get('lang.Product_Add'), $in, 200);
                    }
                }
            } else {
                return errorResponse(Lang::get('lang.invalid'), 400);
            }
        }
    }

    /**
     * Shows the product details from the database.
     *
     * @param
     * @return array of all the products that is present in the database
     */
    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $filter = $request->input('filter_field', 'active');

        $products = $this->buildProductQuery($filter, $searchQuery)
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($product) {
            $product->versions = DB::table('product_versions')
                ->where('product_id', $product->id)
                ->where('version_status', '1')
                ->orderByDesc('version_date')
                ->value('version_number');
            $product->versions_count = DB::table('product_versions')
                ->where('product_id', $product->id)
                ->count();

            return $product;
        });

        return successResponse(Lang::get('lang.Product_Show'), $products, 200);
    }

    /**
     * Deletes the product details from the database by using product id.
     *
     * @param  $id
     * @return response that a product has been deleted  with it's cascaded values
     */
    //delete product
    public function deleteProduct(Request $request)
    {
        $api_error_detected = 0;
        $api_action_success = 0;
        $removed_records = 0;
        $id = $request->get('id');
        $api_key_secret = $request->get('api_key_secret');
        $soft_delete = $request->get('soft_delete');

        if (null !== request()->server('REMOTE_ADDR')) {
            $ip_address = request()->server('REMOTE_ADDR');
        } else {
            $ip_address = $request->ip();
        }

        if (! empty($api_key_secret)) {
            // $api = ApiKeys::where('api_key_secret', $api_key_secret)->where('api_key_status', 1)->get()->toArray();
            if (empty($api)) {
                return errorResponse(Lang::get('lang.invalid_api_key'), 404);
            } else {
                $api_ip = new ApiKeys();
                $api_ips = $api_ip->value('api_key_ip');

                if (! empty($api_ips)) {
                    if (! $api_ips->contains($ip_address)) {
                        $api_error_detected = 1;

                        return errorResponse(Lang::get('lang.Api_Acess_not_allowed'), 400);
                    } else {
                        $api_action_success = 1;
                    }
                } else {
                    $api_action_success = 1;
                }
            }
            if (aflValidateIntegerValue($id)) {
                if ($soft_delete === 0) {
                    DB::beginTransaction(); //mysqli_begin_transaction($GLOBALS["mysqli"]);
                    $transaction_errors_array = [];
                    try {
                        $licenses = DB::table('licenses')->where('product_id', $id)->pluck('license_code');

                        foreach ($licenses as $license_code) {
                            InstallationLog::where('license_code', $license_code)->delete();
                        }

                        DB::table('license_callbacks')->where('product_id', $id)->delete(); //doMysqlQuery("DELETE FROM apl_callbacks WHERE id=?", array($id), array("i")); //delete callbacks

                        DB::table('installations')->where('product_id', $id)->delete(); //doMysqlQuery("DELETE FROM apl_installations WHERE id=?", array($id), array("i")); //delete installations

                        DB::table('licenses')->where('product_id', $id)->delete(); //doMysqlQuery("DELETE FROM apl_licenses WHERE id=?", array($id), array("i")); //delete licenses

                        $removed_records += DB::table('products')->where('id', $id)->delete(); //$removed_records+=doMysqlQuery("DELETE FROM apl_products WHERE id=?", array($id), array("i"));

                        DB::commit();
                    } catch (\Exception $e) {
                        $transaction_errors_array[] = $e->getMessage();
                        DB::rollBack();
                        $removed_records = 0;

                        return errorResponse(Lang::get('lang.invalid'), 400);
                    }
                } else {
                    Product::where('id', $id)->update(['status' => 0]);
                    $removed_records += Product::where('id', $id)->delete();
                }
            }

            return successResponse(Lang::get('lang.Product_Destroy'), $removed_records, 200);
        }
    }

    public function edit($id)
    {
        $product = Product::where('id', $id)->firstOrFail();

        if (! empty($product)) {
            return successResponse('', ['product' => $product], 200);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }

    /**
     * Updates the product details into the database.
     *
     * @param  ProductRequest  $request
     * @param  $api_key_secret
     * @param  $id
     * @param  $name
     * @param  $product_sku
     * @param  $status
     * @param  $product_description
     * @param  $product_url_homepage
     * @param  $product_url_download
     * @param  $product_version
     * @param  $product_envato_id
     * @return response that a product details of the records found is Updated with a success response
     */
    public function productUpdate(Request $request)
    {
        $api_key_secret = $request->get('api_key_secret');
        $id = $request->get('id');
        $name = $request->get('name');
        $product_sku = $request->get('product_sku');
        $status = $request->get('status');
        $product_description = $request->get('product_description');
        $product_url_homepage = $request->get('product_url_homepage');
        $product_url_download = $request->get('product_url_download');
        $product_version = $request->get('product_version');
        $product_envato_id = $request->get('product_envato_id');

        if (empty($id) || ! aflValidateIntegerValue($id) || empty($rows_array = Product::where('id', $id)->get()->toArray())) { //invalid record
            return errorResponse(Lang::get('lang.invalid'), 404);
        }

        $api_action_success = 0;
        $api_error_detected = 0;
        $updated_records = 0;
        if (null !== request()->server('REMOTE_ADDR')) {
            $ip_address = request()->server('REMOTE_ADDR');
        } else {
            $ip_address = $request->ip();
        }

        if (! empty($api_key_secret)) {
            // $api = ApiKeys::where('api_key_secret', $api_key_secret)->where('api_key_status', 1)->get()->toArray();
            if (empty($api)) {
                return errorResponse(Lang::get('lang.invalid_api_key'), 404);
            } else {
                $api_ip = new ApiKeys();
                $api_ips = $api_ip->value('api_key_ip');

                if (! empty($api_ips)) {
                    if (! $api_ips->contains($ip_address)) {
                        $api_error_detected = 1;

                        return errorResponse(Lang::get('lang.Api_Acess_not_allowed'), 400);
                    } else {
                        $api_action_success = 1;
                    }
                } else {
                    $api_action_success = 1;
                }
            }
            if (! empty($name) && ! empty($product_sku) && aflValidateIntegerValue($status, 0, 2) && $api_action_success == 1) {
                if (! empty($product_url_homepage) && ! filter_var($product_url_homepage, FILTER_VALIDATE_URL)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.url_error'), 400);
                }

                if (! empty($product_envato_id) && ! aflValidateIntegerValue($product_envato_id)) {
                    $api_error_detected = 1;

                    return errorResponse(Lang::get('lang.envato_error'), 400);
                }

                if ($api_error_detected != 1) {
                    if (! aflValidateIntegerValue($product_envato_id)) {
                        $product_envato_id = null;
                    }

                    $updated_records += DB::table('afl_products')
                        ->where('id', $id)
                        ->update([
                            'name' => $name,
                            'product_description' => $product_description,
                            'product_sku' => $product_sku,
                            'product_url_homepage' => $product_url_homepage,
                            'product_url_download' => $product_url_download,
                            'product_version' => $product_version,
                            'product_envato_id' => $product_envato_id,
                            'status' => $status,
                        ]);

                    if (! aflValidateIntegerValue($updated_records)) {
                        $api_error_detected = 1;

                        return errorResponse(Lang::get('lang.nothing_updated'), 400);
                    } else {
                        return successResponse(Lang::get('lang.Product_Update'), $updated_records, 200);
                    }
                }
            } else {
                return errorResponse(Lang::get('lang.invalid'), 400);
            }
        }
    }

    public function addAflAndAfuProduct(ProductRequest $request)
    {
        try {
            $response = $this->productAdd($request);
            $response = json_decode($response->getContent());
            if ($response->success == true) {
                $productId = Product::where('product_sku', $request->get('product_sku'))->pluck('id')->first();
                if ($productId) {
                    $afuResponse = $this->addNewProductToAUS($request, $productId);
                    if ($afuResponse->success == false) {
                        return errorResponse(Lang::get('lang.invalid'), 400);
                    }

                    return successResponse(Lang::get('lang.Product_Add'));
                } else {
                    return errorResponse(Lang::get('lang.invalid'), 400);
                }
            } else {
                return errorResponse($response->message, 400);
            }
        } catch (\Exception $e) {
            // Return an error response
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    private function addNewProductToAUS($request, $productId)
    {
        try {
            $key = str_random(16); // Generate a random product key

            $customRequest = new Request(array_merge($request->all(), ['product_key' => $key, 'id' => $productId]));

            // Use dependency injection instead of creating a new instance
            $afuProduct = app(AfuProductsController::class);
            $response = $afuProduct->productUpdateAdd($customRequest);

            return json_decode($response->getContent());
        } catch (\Exception $ex) {
            // Throw an exception with a specific error message
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    public function updateAflAndAfuProduct(Request $request)
    {
        try {
            $responseFromProduct = $this->productUpdate($request);
            $response = json_decode($responseFromProduct->getContent());
            // Check if the response indicates success
            if ($response->success == true) {
                $this->updateProductToAUS($request);

                return successResponse(Lang::get('lang.Product_Update'));
            } else {
                // Return an error response if the API call was not successful
                return errorResponse($response->message, 400);
            }
        } catch (\Exception $e) {
            // Return an error response
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    private function updateProductToAUS($request)
    {
        try {
            // Fetch the product key from the database
            $key = Product::where('id', $request->get('id'))->pluck('product_key')->first();

            // Prepare the data for the custom request
            $data = $request->all(); // Get all request data

            // Remove 'product_url_homepage' if it is empty
            if (empty($request->get('product_url_homepage'))) {
                unset($data['product_url_homepage']);
            }

            // Merge the product key into the data array
            $data['product_key'] = $key;

            // Use dependency injection to resolve the controller instance
            $afuProduct = app(AfuProductsController::class);

            // Call the method on the controller
            $response = $afuProduct->productUpdateUpdate(new Request($data));

            // Return the response content as a JSON-decoded array
            return json_decode($response->getContent());
        } catch (\Exception $ex) {
            // Handle the exception and return an error response
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    public function deleteAflAndAfuProduct(Request $request)
    {
        try {
            $responseFromProduct = $this->deleteProduct($request);
            $response = json_decode($responseFromProduct->getContent());
            // Check if the response indicates success
            if ($response->success == true) {
                $afuProduct = app(AfuProductsController::class);
                $afuProduct->deleteUpdateProduct($request);

                return successResponse(Lang::get('lang.product_suspended'));
            } else {
                // Return an error response if the API call was not successful
                return errorResponse($response->message, 400);
            }
        } catch (\Exception $e) {
            // Return an error response
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    private function buildProductQuery($filter, $searchQuery)
    {
        $products = Product::select('id', 'name', 'product_sku', 'status')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('licenses')
                    ->whereColumn('licenses.product_id', 'products.id');
            }, 'licenses_count')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('installations')
                    ->whereColumn('installations.product_id', 'products.id');
            }, 'installations_count');

        // Filter products
        if ($filter === 'suspended') {
            $products->where('status', '!=', 1);
        } elseif ($filter === 'active') {
            $products->where('status', 1);
        }

        // Apply search query
        if ($searchQuery) {
            $products->where(function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', '%'.$searchQuery.'%')
                    ->orWhere('product_sku', 'LIKE', '%'.$searchQuery.'%')
                    ->orWhere('status', 'LIKE', '%'.statusFormatter($searchQuery).'%');
            });
        }

        return $products;
    }

    /**
     * Restore a suspended product (It restores the product from both Product and Product).
     *
     * @param  Request  $request
     * @return Response
     */
    public function restoreSuspendedProduct(Request $request)
    {
        $id = $request->input('id');
        $api_key_secret = $request->input('api_key_secret');
        $api_key = new ApiKeysController();

        if (null !== request()->server('REMOTE_ADDR')) {
            $ip_address = request()->server('REMOTE_ADDR');
        } else {
            $ip_address = $request->ip();
        }

        // Validate id and API key
        if (! aflValidateIntegerValue($id) || ! $api_key->apiKeyCheck($api_key_secret, $ip_address)) {
            return errorResponse(Lang::get('lang.invalid'), 400);
        }

        // Retrieve the products
        $aflProduct = Product::find($id);

        if (empty($aflProduct)) {
            return errorResponse(Lang::get('lang.invalid_product'), 400);
        }

        // Change the status after restoration
        $aflProduct->status = 1;

        // Save the updated product
        $aflProduct->save();

        return successResponse(Lang::get('lang.product_restored'), 1, 200);
    }

    public function getProductIdbyKey(Request $request)
    {
        return Product::where('product_key', $request->product_key)->value('id');
    }
}
