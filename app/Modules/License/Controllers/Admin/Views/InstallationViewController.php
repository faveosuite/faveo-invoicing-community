<?php

namespace App\Modules\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\Modules\License\Models\Installation;
use App\Modules\License\Models\LicenseCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class InstallationViewController extends Controller
{
    public function getInstallation($id)
    {
        $installation = DB::table('installations')
            ->selectRaw('installations.id, installations.product_id, installations.user_id as client_id, installations.license_code, installations.installation_ip, installations.installation_domain, installations.installation_date, installations.installation_status, products.name as product_title, products.id as product_id, users.email as client_email, licenses.id as license_id')
            ->leftJoin('products', 'installations.product_id', '=', 'products.id')
            ->leftJoin('users', 'installations.user_id', '=', 'users.id')
            ->leftJoin('licenses', 'installations.license_code', '=', 'licenses.license_code')
            ->where('installations.id', $id)
            ->first();

        return successResponse(Lang::get('lang.installation_details'), $installation);
    }

    public function getInstallationCallbacks(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'callback_id');
        $installationDomain = Installation::where('id', $id)->value('installation_domain');
        $callbacks = LicenseCallback::where('callback_domain', $installationDomain)
            ->select('callback_id', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('callback_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_domain', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.installation_callbacks'), $callbacks);
    }
}
