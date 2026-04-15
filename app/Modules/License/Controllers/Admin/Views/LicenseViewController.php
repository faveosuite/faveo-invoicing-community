<?php

namespace App\Modules\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\Modules\License\Models\InstallationLog;
use App\Modules\License\Models\LicenseCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class LicenseViewController extends Controller
{
    public function getLicenseDetails($license_id)
    {
        $license = DB::table('licenses')
            ->selectRaw('licenses.id, licenses.product_id, licenses.user_id as client_id, licenses.license_ip, licenses.license_code, licenses.license_limit, licenses.license_expire_date, licenses.license_support_date, licenses.license_order_number, licenses.license_domain, licenses.license_date, licenses.license_updates_date, licenses.license_status, products.name as product_title, products.id as product_id, users.email as client_email')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.id', $license_id)
            ->first();
        $license->license_order_url = $license->license_order_number ?? '';
        $license->installation_counts = DB::table('installations')
            ->where('license_code', $license->license_code)
            ->count();
        $license->latest_call_backs = DB::table('license_callbacks')
            ->where('license_code', $license->license_code)
            ->orderByDesc('callback_date_time')
            ->value('callback_date_time');
        $license->call_backs_count = DB::table('license_callbacks')
            ->where('license_code', $license->license_code)
            ->count();

        return successResponse(Lang::get('lang.license_details'), $license);
    }

    public function getLicenseInstallations(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $license = DB::table('licenses')->select('id', 'id', 'user_id as client_id', 'license_code')->where('id', $license_id)->first();
        $licenseInstallations = DB::table('installations')
            ->select('id', 'user_id as client_id', 'id', 'installation_domain', 'installation_ip', 'installation_date', 'installation_status')
            ->when($license->license_code, function ($query) use ($license) {
                $query->where('license_code', $license->license_code);
            })
            ->when($license->client_id, function ($query) use ($license) {
                $query->where('user_id', $license->client_id);
            })
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->Where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('installation_status', 'LIKE', '%'.statusFormatter($searchQuery).'%')
                        ->orWhere('installation_date', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.license_installations'), $licenseInstallations);
    }

    public function getLicenseCallBacks(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'callback_id');
        $license = DB::table('licenses')->select('id', 'id', 'user_id as client_id', 'license_code')->where('id', $license_id)->first();
        $licenseCallBacks = LicenseCallback::where('id', $license->id)
        ->where('client_id', $license->client_id)
        ->Where('license_code', $license->license_code)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('callback_domain', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.successErrorFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'LIKE', '%'.$searchQuery.'%');
                });
            })
        ->orderBy($sortField, $sortOrder)
        ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.license_callback'), $licenseCallBacks);
    }

    public function getLicenseInstallationLogs(Request $request, $license_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'installation_last_active_date');
        $license = DB::table('licenses')->select('id', 'license_code')->where('id', $license_id)->first();
        $installationLogs = InstallationLog::where('license_code', $license->license_code)
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where('installation_domain', 'LIKE', '%'.$searchQuery.'%')
                    ->orWhere('installation_ip', 'LIKE', '%'.$searchQuery.'%');
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse('',$installationLogs);
    }
}
