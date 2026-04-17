<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\ProductVersion;
use Illuminate\Http\Request;

class VersionsController extends Controller
{
    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $allowedSortFields = ['id', 'product_id', 'version_number', 'version_date', 'version_status'];
        $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'id';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $versions = ProductVersion::query()
            ->with(['product:id,name'])
            ->withCount('callbacks as callback_count')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('version_number', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('version_date', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('version_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhereHas('product', function ($productQuery) use ($searchQuery) {
                            $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                        });
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $versions->getCollection()->transform(function (ProductVersion $version) {
            return [
                'id' => $version->id,
                'product_id' => $version->product_id,
                'version_number' => $version->version_number,
                'version_date' => $version->version_date,
                'version_status' => $version->version_status,
                'product_title' => optional($version->product)->name,
                'callback_count' => $version->callback_count,
            ];
        });

        return successResponse('', $versions);
    }
}
