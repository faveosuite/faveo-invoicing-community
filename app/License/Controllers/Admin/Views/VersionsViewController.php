<?php

namespace App\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\Model\Product\ProductUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class VersionsViewController extends Controller
{
    public function getVersionInfo($version_id)
    {
        $version = ProductUpload::with('product:id,name')
            ->find($version_id);

        if (! $version) {
            return successResponse(Lang::get('lang.version_details'), null);
        }

        return successResponse(Lang::get('lang.version_details'), [
            'id' => $version->id,
            'product_id' => $version->product_id,
            'version_number' => $version->version,
            'version_date' => $version->created_at,
            'version_status' => $version->status,
            'version_install_count' => $version->version_install_count ?? 0,
            'product' => [
                'id' => optional($version->product)->id,
                'name' => optional($version->product)->name,
            ],
            'product_title' => optional($version->product)->name,
        ]);
    }

    public function getVersionCallbacks(Request $request, $version_id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $versionInstallation = ProductUpload::find($version_id)
            ->callbacks()
            ->select('id', 'version_id', 'callback_ip', 'callback_date_time', 'callback_status', 'callback_type')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($query) use ($searchQuery) {
                    $query->where('callback_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        return successResponse(Lang::get('lang.version_callbacks'), $versionInstallation);
    }
}
