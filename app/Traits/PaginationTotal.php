<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait PaginationTotal
{
    /**
     * Returns the total count for a query, using cache when no filters are active.
     *
     * Unscoped (model class) — cache key derived from model name:
     *   $total = $this->cachedTotal(User::class, $request, ['country', 'role']);
     *
     * Scoped (builder with backend constraints) — pass an explicit cache key:
     *   $total = $this->cachedTotal($query, $request, ['category'], cacheKey: 'active_products_total');
     *
     * @param  string|Builder  $model  Eloquent model class or Builder with backend scope
     * @param  Request  $request
     * @param  array  $filterKeys  Request keys that indicate user filtering is active
     * @param  string  $searchKey  Request key for search input (default: 'search-query')
     * @param  int  $ttl  Cache TTL in seconds (default: 60)
     * @param  string|null  $cacheKey  Override the cache key (required when passing a Builder)
     */
    protected function cachedTotal(
        string|Builder $model,
        Request $request,
        array $filterKeys = [],
        string $searchKey = 'search-query',
        int $ttl = 60,
        ?string $cacheKey = null
    ): ?int {
        $isFiltered = $request->input($searchKey, '') !== ''
            || $request->hasAny($filterKeys);

        if ($isFiltered) {
            return null;
        }

        $modelClass = $model instanceof Builder
            ? $model->getModel()::class
            : $model;

        $resolvedKey = $cacheKey ?? 'pagination_total_'.Str::snake(class_basename($modelClass));

        $counter = $model instanceof Builder
            ? (clone $model)->count(...)
            : fn () => $modelClass::count();

        return Cache::remember($resolvedKey, $ttl, $counter);
    }

    /**
     * Build the paginated success response with an optional total count attached.
     *
     * Usage:
     *   return $this->paginateResponse($users, $total);
     */
    protected function paginateResponse($paginator, ?int $total)
    {
        $response = collect($paginator->toArray())->put('total', $total);

        return successResponse('', $response);
    }
}
