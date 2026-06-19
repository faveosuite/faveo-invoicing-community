<?php

namespace App\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\VersionCallback;
use App\Model\Product\ProductUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class VersionsViewController extends Controller
{
    public function getVersionInfo(mixed $version_id): \Illuminate\Http\JsonResponse
    {
        $version = ProductUpload::with('product:id,name')
            ->find($version_id);

        if (! $version) {
            return successResponse(__('lang.version_details'), data: null);
        }

        return successResponse(__('lang.version_details'), [
            'id' => $version->id,
            'product_id' => $version->product_id,
            'version_number' => $version->version,
            'version_date' => $version->created_at,
            'version_status' => $version->status,
            'version_install_count' => $version->version_install_count ?? 0,
            'product' => [
                'id' => $version->product?->id,
                'name' => $version->product?->name,
            ],
            'product_title' => $version->product?->name,
        ]);
    }

    public function getVersionCallbacks(Request $request, mixed $version_id): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $versionInstallation = ProductUpload::find($version_id)
            ->callbacks()
            ->select('id', 'version_id', 'callback_ip', 'callback_date_time', 'callback_status', 'callback_type')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($query) use ($searchQuery): void {
                    $query->where('callback_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $versionInstallation->getCollection()->transform(fn (VersionCallback $cb): array => [ // @phpstan-ignore argument.type
            'id' => $cb->id,
            'version_id' => $cb->version_id,
            'callback_ip' => $cb->callback_ip,
            'callback_date_time' => $cb->callback_date_time,
            'callback_status' => $cb->callback_status,
            'callback_type' => $cb->callback_type,
        ]);

        return successResponse(__('lang.version_callbacks'), $versionInstallation);
    }
}
