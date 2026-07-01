<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Product\ProductUpload;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', $request->input('perPage', 10));
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search-query', $request->input('search-query', $request->input('search_query', '')));
        $sortOrder = $request->input('sort-order', $request->input('sort_order', 'desc'));
        $sortField = $request->input('sort-field', $request->input('sort_field', 'id'));
        $allowedSortFields = ['id', 'product_id', 'version', 'created_at', 'status'];
        $sortField = in_array($sortField, $allowedSortFields, strict: true) ? $sortField : 'id';
        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';

        $versions = ProductUpload::query()
            ->with(['product:id,name'])
            ->withCount('callbacks as callback_count')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->where('version', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('created_at', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhereHas('product', function (Builder $productQuery) use ($searchQuery): void {
                            $productQuery->where('name', 'LIKE', '%'.$searchQuery.'%');
                        });
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $versions->getCollection()->transform(fn (ProductUpload $version): array => [ // @phpstan-ignore method.unresolvableReturnType, argument.unresolvableType
            'id' => $version->id,
            'product_id' => $version->product_id,
            'version_number' => $version->version,
            'version_date' => $version->created_at,
            'version_status' => $version->status,
            'version_install_count' => $version->version_install_count ?? 0,
            'product_title' => $version->product?->name,
            'callback_count' => $version->callback_count, // @phpstan-ignore property.notFound
        ]);

        return successResponse('', $versions);
    }
}
