<?php

namespace App\Modules\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\Modules\License\Models\Installation;
use App\Modules\License\Models\License;
use App\Model\Product\Product;
use App\Modules\License\Models\ProductVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class ProductsViewController extends Controller
{
    public function getProductDetails($id)
    {
        $product = Product::select('id', 'name', 'product_sku', 'product_url_homepage', 'product_url_download', 'status')
            ->find($id);
        $product->versions = DB::table('product_versions')
            ->where('product_id', $id)
            ->where('version_status', '1')
            ->orderByDesc('version_date')
            ->value('version_number');
        return successResponse(Lang::get('lang.product_details'),$product);
    }
    public function getProductInstallations(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $productInstallations = DB::table('installations')
            ->selectRaw('installations.id, installations.user_id as client_id, installations.license_code, installations.installation_domain, installations.installation_ip, installations.installation_date, installations.installation_status, users.email as client_email, licenses.id as license_id')
            ->leftJoin('users', 'installations.user_id', '=', 'users.id')
            ->leftJoin('licenses', 'installations.license_code', '=', 'licenses.license_code')
            ->where('installations.product_id', $id)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('users.email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('installations.license_code', 'like', '%' . str_replace("-", "",$searchQuery)  . '%')
                        ->orWhere('installations.installation_domain', 'like', '%' . $searchQuery . '%')
                        ->orWhere('installations.installation_ip', 'like', '%' . $searchQuery . '%')
                        ->orWhere('installations.installation_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                        ->orWhere('installations.installation_date', 'like', '%' . $searchQuery . '%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.product_installations'), $productInstallations);
    }

    public function getProductLicenses(Request $request, $id){
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $productLicenses = DB::table('licenses')
            ->selectRaw('licenses.id, licenses.user_id as client_id, licenses.license_code, licenses.license_order_number, licenses.license_date, licenses.license_expire_date, licenses.license_updates_date, licenses.license_support_date, licenses.license_status, users.email as client_email')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.product_id', $id)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('users.email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('licenses.license_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                        ->orWhere('licenses.license_code', 'like', '%' . str_replace("-", "",$searchQuery)  . '%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $productLicenses->getCollection()->transform(function ($license) {
            $license->installation_counts = DB::table('installations')
                ->where('license_code', $license->license_code)
                ->count();
            $license->latest_call_backs = DB::table('license_callbacks')
                ->where('license_code', $license->license_code)
                ->orderByDesc('callback_date_time')
                ->value('callback_date_time');
            $license->license_order_url = $license->license_order_number ?? '';
            return $license;
        });
        return successResponse(Lang::get('lang.product_licenses'), $productLicenses);
    }
    public function getProductVersions(Request $request, $productId)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $aflProduct = Product::find($productId);
        $product = Product::where('product_sku',$aflProduct->product_sku)->value('id');
        $productVersions = ProductVersion::where('product_id', $product)
            ->where(function ($query) use ($searchQuery) {
                $query->where('version_number', 'like', '%' . $searchQuery . '%')
                    ->orWhere('version_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                    ->orWhere('version_date', 'like', '%' . $searchQuery . '%');
            })
            ->select('version_id', 'id', 'version_number', 'version_date', 'version_upgrade_count', 'version_status')
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        return successResponse(Lang::get('lang.product_versions'), $productVersions);
    }
}
