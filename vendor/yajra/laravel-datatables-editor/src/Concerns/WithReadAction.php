<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait WithReadAction
{
    /**
     * Process read action request.
     */
    public function read(Request $request): JsonResponse
    {
        $ids = $request->array('ids');
        $model = $this->resolveModel();
        $data = $model->newQuery()
            ->whereIn($model->getKeyName(), $ids)
            ->get()
            ->map(function (Model $model) {
                $model->setAttribute('DT_RowId', (string) $model->getKey());

                return $model;
            })
            ->toArray();

        return $this->toJson($data);
    }
}
