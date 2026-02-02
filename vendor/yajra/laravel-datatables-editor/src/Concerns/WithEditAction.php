<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @template TModel of Model
 */
trait WithEditAction
{
    /**
     * Process edit action request.
     */
    public function edit(Request $request): JsonResponse
    {
        $connection = $this->getBuilder()->getConnection();
        $affected = [];
        $errors = [];

        $connection->beginTransaction();
        foreach ($this->dataFromRequest($request) as $key => $data) {
            $this->currentData = $data;

            $model = $this->getBuilder()->findOrFail($key);
            $validator = $this->getValidationFactory()
                ->make(
                    $data,
                    $this->editRules($model), $this->messages() + $this->editMessages(),
                    $this->attributes()
                );
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                }

                continue;
            }

            $data = $this->updating($model, $data);

            $data = $this->saving($model, $data);

            if ($this->restoring && method_exists($model, 'restore')) {
                $model->restore();
            } else {
                $model->fill($data)->save();
            }

            $model = $this->updated($model, $data);

            $model = $this->saved($model, $data);

            $model->setAttribute('DT_RowId', $model->getKey());
            $affected[] = $model;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        return $this->toJson($affected, $errors);
    }

    /**
     * Get edit action validation rules.
     *
     * @param  TModel  $model
     */
    public function editRules(Model $model): array
    {
        return [];
    }

    /**
     * Get edit validation messages.
     */
    protected function editMessages(): array
    {
        return [];
    }

    /**
     * @param  TModel  $model
     */
    public function updating(Model $model, array $data): array
    {
        return $data;
    }

    /**
     * @param  TModel  $model
     * @return TModel
     */
    public function updated(Model $model, array $data): Model
    {
        return $model;
    }
}
