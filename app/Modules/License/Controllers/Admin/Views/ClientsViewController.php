<?php

namespace App\Modules\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class ClientsViewController extends Controller
{
    public function getClientInfo($client_id)
    {
        $client = DB::table('users')
            ->select('id as client_id', 'email as client_email', 'role as client_role', 'created_at as client_active_date', 'profile_pic as client_profile_pic', 'address as client_address', 'company as client_organization', 'active as client_status')
            ->selectRaw('id as client_id, email as client_email, role as client_role, created_at as client_active_date, profile_pic as client_profile_pic, address as client_address, company as client_organization, active as client_status, CONCAT(first_name, " ", last_name) as full_name')
            ->where('id', $client_id)
            ->first();

        return successResponse(Lang::get('lang.client_details'), $client);
    }

    public function getClientInstallations(Request $request, $client_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $clientInstallations = DB::table('installations')
            ->selectRaw('installations.id, installations.user_id as client_id, installations.installation_domain, installations.installation_ip, installations.installation_date, installations.installation_status, products.name as product_title, products.id as product_id')
            ->leftJoin('products', 'installations.product_id', '=', 'products.id')
            ->where('installations.user_id', $client_id)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('installations.installation_domain', 'like', '%'.$searchQuery.'%')
                        ->orWhere('installations.installation_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('installations.installation_status', 'LIKE', '%'.statusFormatter($searchQuery).'%')
                        ->orWhere('installations.installation_date', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.client_installations'), $clientInstallations);
    }

    public function getClientLicenses(Request $request, $client_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $productLicenses = DB::table('licenses')
            ->selectRaw('licenses.id, licenses.product_id, licenses.user_id as client_id, licenses.license_code, licenses.license_order_number, licenses.license_date, licenses.license_expire_date, licenses.license_updates_date, licenses.license_support_date, licenses.license_status, products.name as product_title, products.id as product_id')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->where('licenses.user_id', $client_id)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('licenses.license_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('licenses.license_expire_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('licenses.license_updates_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('licenses.license_support_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('licenses.license_status', 'LIKE', '%'.statusFormatter($searchQuery).'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        $productLicenses->getCollection()->transform(function ($license) {
            $license->latest_call_backs = DB::table('license_callbacks')
                ->where('license_code', $license->license_code)
                ->orderByDesc('callback_date_time')
                ->value('callback_date_time');
            $license->installation_counts = DB::table('installations')
                ->where('license_code', $license->license_code)
                ->count();
            $license->license_order_url = $license->license_order_number ?? '';

            return $license;
        });

        return successResponse(Lang::get('lang.client_licenses'), $productLicenses);
    }
}
