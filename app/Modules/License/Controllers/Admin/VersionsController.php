<?php

namespace App\Modules\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\License\Models\ProductVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersionsController extends Controller
{
    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder= $request->input('sort_order','desc');
        $sortField = $request->input('sort_field','id');
        $versions = DB::table('product_versions')
            ->selectRaw('product_versions.version_id, product_versions.id, product_versions.version_number, product_versions.version_date, product_versions.version_upgrade_count, product_versions.version_status, products.name as name')
            ->leftJoin('products', 'product_versions.id', '=', 'products.id')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('version_callbacks')
                    ->whereColumn('version_callbacks.version_id', 'product_versions.version_id');
            }, 'callback_count')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('product_versions.version_number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('product_versions.version_date', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('product_versions.version_status', 'LIKE', '%' . statusFormatter($searchQuery) . '%')
                        ->orWhere('products.name', 'LIKE', '%' . $searchQuery . '%');
                });
            })
            ->orderBy('product_versions.'.$sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        return successResponse('',$versions);
    }
}
