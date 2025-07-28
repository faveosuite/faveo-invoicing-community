<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait WithCreateAction
{
    /**
     * Process create action request.
     *
     * @throws \Throwable
     */
    public function create(Request $request): JsonResponse
    {
        $model = $this->resolveModel();
        $connection = $model->getConnection();
        $affected = [];
        $errors = [];

        $connection->beginTransaction();
        foreach ($this->dataFromRequest($request) as $data) {
            $this->currentData = $data;

            $instance = $model->newInstance();
            $validator = $this->getValidationFactory()
                ->make(
                    $data,
                    $this->createRules(), $this->messages() + $this->createMessages(),
                    $this->attributes()
                );
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                }

                continue;
            }

            $data = $this->creating($instance, $data);

            $data = $this->saving($instance, $data);

            $instance->fill($data)->save();

            $instance = $this->created($instance, $data);

            $instance = $this->saved($instance, $data);

            $instance->setAttribute('DT_RowId', $instance->getKey());
            $affected[] = $instance;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        return $this->toJson($affected, $errors);
    }

    /**
     * Get create action validation rules.
     */
    public function createRules(): array
    {
        return [];
    }

    /**
     * Get create validation messages.
     */
    protected function createMessages(): array
    {
        return [];
    }

    public function creating(Model $model, array $data): array
    {
        return $data;
    }

    public function created(Model $model, array $data): Model
    {
        return $model;
    }
}
