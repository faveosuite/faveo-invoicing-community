<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @template TModel of Model
 */
trait WithRemoveAction
{
    /**
     * Process remove action request.
     */
    public function remove(Request $request): JsonResponse
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
                    $this->removeRules($model), $this->messages() + $this->removeMessages(),
                    $this->attributes()
                );
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error['status'];
                }

                continue;
            }

            try {
                $deleted = clone $model;
                $data = $this->deleting($model, $data);

                $this->forceDeleting ? $model->forceDelete() : $model->delete();

                $deleted = $this->deleted($deleted, $data);
            } catch (QueryException $exception) {
                $error = config('app.debug')
                    ? $exception->getMessage()
                    : $this->removeExceptionMessage($exception, $model);

                $errors[] = $error;
            }

            $affected[] = $deleted;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        return $this->toJson($affected, [], $errors);
    }

    /**
     * Get remove action validation rules.
     *
     * @param  TModel  $model
     */
    public function removeRules(Model $model): array
    {
        return [];
    }

    /**
     * Get remove validation messages.
     */
    protected function removeMessages(): array
    {
        return [];
    }

    /**
     * @param  TModel  $model
     */
    public function deleting(Model $model, array $data): array
    {
        return $data;
    }

    /**
     * @param  TModel  $model
     * @return TModel
     */
    public function deleted(Model $model, array $data): Model
    {
        return $model;
    }

    /**
     * Get remove query exception message.
     */
    protected function removeExceptionMessage(QueryException $exception, Model $model): string
    {
        // @phpstan-ignore-next-line
        return "Record {$model->getKey()} is protected and cannot be deleted!";
    }
}
