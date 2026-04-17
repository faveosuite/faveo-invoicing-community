<?php

namespace App\License\Controllers\Update;

// ApiKeysController removed
use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\ProductVersion;
use App\License\Models\VersionCallback;
use App\Model\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class AfuProductsController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    /**
     * stores the product details into the database.
     *
     * @param  Request  $request
     * @param  $api_key_secret
     * @param  $product_title
     * @param  $product_sku
     * @param  $product_status
     * @param  $product_description
     * @param  $product_url_homepage
     * @param  $product_url_download
     * @param  $product_version
     * @param  $product_envato_id
     * @return response that a product details is added with a success response
     */
    public function productUpdateAdd(Request $request)
    {
        $api_error_detected = 0;
        $added_records = 0;
        $api_key_secret = $request->get('api_key_secret');
        $product_id = $request->get('product_id');
        $product_title = $request->get('product_title');
        $product_sku = $request->get('product_sku');
        $product_short_description = $request->get('product_short_description');
        $product_full_description = $request->get('product_full_description');
        $product_key = $request->get('product_key');
        $product_status = $request->get('product_status');
        $product_url_homepage = $request->get('product_url_homepage');
        $product_url_order = $request->get('product_url_order');
        $product_price = $request->get('product_price');
        $product_max_active_versions = $request->get('product_max_active_versions');

        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        $optional_api_parameters_array = ['product_short_description', 'product_full_description', 'product_url_homepage', 'product_url_order', 'product_price', 'product_max_active_versions']; //optional API parameters for this page
        foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
            if (! isset($$optional_api_parameter)) {
                $$optional_api_parameter = '';
            }
        }

        if (! empty($product_title) && ! empty($product_sku) && LicenseHelper::validateIntegerValue($product_status, 0, 2) && ! empty($product_key) && $api_action_success == 1) {
            if (! empty($product_url_homepage) && ! filter_var($product_url_homepage, FILTER_VALIDATE_URL)) {
                $api_error_detected = 1;

                return errorResponse(Lang::get('lang.error_producturl'), 400);
            }

            if ($api_error_detected != 1) {
                $product_date = date('Y-m-d');
                try {
                    $in = DB::table('afu_products')->insertOrIgnore([
                        'product_id' => $product_id,
                        'product_title' => $product_title,
                        'product_sku' => $product_sku,
                        'product_short_description' => $product_short_description,
                        'product_full_description' => $product_full_description,
                        'product_key' => $product_key,
                        'product_url_homepage' => $product_url_homepage,
                        'product_url_order' => $product_url_order,
                        'product_price' => $product_price,
                        'product_date' => $product_date,
                        'product_status' => $product_status,
                        'product_max_active_versions' => $product_max_active_versions,
                    ]);
                    $added_records += 1;
                } catch (Exception $e) {
                    $added_records += 0;
                }
                if (! LicenseHelper::validateIntegerValue($added_records)) {
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

    public function deleteUpdateProduct(Request $request)
    {
        $removed_records = 0;
        $product_id = $request->get('product_id');
        $api_key_secret = $request->get('api_key_secret');
        $soft_delete = $request->get('soft_delete');
        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        if (LicenseHelper::validateIntegerValue($product_id) && $api_action_success == 1) {
            if ($soft_delete === 0) {
                DB::beginTransaction(); //mysqli_begin_transaction($GLOBALS["mysqli"]);
                $transaction_errors_array = [];
                try {
                    VersionCallback::where('product_id', $product_id)->delete(); //Delete all callback for that product
                    Installation::where('product_id', $product_id)->delete(); //Delete all installations for that product
                    ProductVersion::where('product_id', $product_id)->delete(); //Delete all versions for that product
                    $removed_records += Product::where('product_id', $product_id)->forceDelete(); //Delete the product
                    DB::commit();
                } catch (\Exception $e) {
                    $transaction_errors_array[] = $e->getMessage();
                    DB::rollBack();
                    $removed_records = 0;

                    return errorResponse(Lang::get('lang.invalid'), 400);
                }
            } else {
                Product::where('product_id', $product_id)->update(['product_status' => 0]);
                $removed_records += Product::where('product_id', $product_id)->delete(); //Delete the product
            }
        }

        return successResponse(Lang::get('lang.delete'), $removed_records, 200);
    }

    public function productUpdateUpdate(Request $request)
    {
        $api_action_success = 0;
        $api_error_detected = 0;
        $updated_records = 0;
        $api_key_secret = $request->get('api_key_secret');
        $product_id = $request->get('product_id');
        $product_title = $request->get('product_title');
        $product_sku = $request->get('product_sku');
        $product_short_description = $request->get('product_short_description');
        $product_full_description = $request->get('product_full_description');
        $product_key = $request->get('product_key');
        $product_status = $request->get('product_status');
        $product_url_homepage = $request->get('product_url_homepage');
        $product_url_order = $request->get('product_url_order');
        $product_price = $request->get('product_price');
        $product_max_active_versions = $request->get('product_max_active_versions');

        if (empty($product_id) || ! LicenseHelper::validateIntegerValue($product_id) || empty($rows_array = Product::where('product_id', $product_id)->get()->toArray())) { //invalid record
            return errorResponse(Lang::get('lang.invalid'), 404);
        }
        $api_key = new ApiKeysController();
        $api_action_success = $api_key->apiKeyCheck($api_key_secret, $this->ip_address);
        if (! empty($product_title) && ! empty($product_sku) && ! empty($product_key) && LicenseHelper::validateIntegerValue($product_status, 0, 2) && $api_action_success == 1) {
            if (! empty($product_url_homepage) && ! filter_var($product_url_homepage, FILTER_VALIDATE_URL)) {
                $api_error_detected = 1;

                return errorResponse(Lang::get('lang.url_error'), 400);
            }

            if ($api_error_detected != 1) {
                $updated_records += DB::table('afu_products')
                    ->where('product_id', $product_id)
                    ->update([
                        'product_title' => $product_title,
                        'product_sku' => $product_sku,
                        'product_short_description' => $product_short_description,
                        'product_full_description' => $product_full_description,
                        'product_key' => $product_key,
                        'product_url_homepage' => $product_url_homepage,
                        'product_url_order' => $product_url_order,
                        'product_price' => $product_price,
                        'product_status' => $product_status,
                        'product_max_active_versions' => $product_max_active_versions,

                    ]);
                if (! LicenseHelper::validateIntegerValue($updated_records)) {
                    return errorResponse(Lang::get('lang.nothing_updated'), 400);
                } else {
                    return successResponse(Lang::get('lang.Product_Update'), $updated_records, 200);
                }
            }
        } else {
            return errorResponse(Lang::get('lang.invalid'), 400);
        }
    }

    public function getProducts(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $product = Product::orderBy($sortField, $sortOrder)
            ->where('product_title', 'LIKE', '%'.$searchQuery.'%')
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse('', $product);
    }
}
