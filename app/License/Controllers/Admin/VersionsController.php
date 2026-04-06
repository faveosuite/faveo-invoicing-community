<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\Model\Product\ProductUpload;
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
        $allowedSortFields = ['id', 'product_id', 'version', 'created_at', 'status'];
        $sortField = in_array($sortField, $allowedSortFields, true) ? $sortField : 'id';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $versions = ProductUpload::query()
            ->with(['product:id,name'])
            ->withCount('callbacks as callback_count')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('version', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('created_at', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhereHas('product', function ($productQuery) use ($searchQuery) {
                            $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                        });
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $versions->getCollection()->transform(function (ProductUpload $version) {
            return [
                'id' => $version->id,
                'product_id' => $version->product_id,
                'version_number' => $version->version,
                'version_date' => LicenseHelper::formatDatetime($version->created_at),
                'version_status' => $version->status,
                'version_install_count' => $version->version_install_count ?? 0,
                'product_title' => optional($version->product)->name,
                'callback_count' => $version->callback_count,
            ];
        });

        return successResponse('', $versions);
    }
}
