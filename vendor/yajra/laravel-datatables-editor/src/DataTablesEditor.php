<?php

declare(strict_types=1);

namespace Yajra\DataTables;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @template TModel of Model
 */
abstract class DataTablesEditor
{
    use Concerns\WithCreateAction;
    use Concerns\WithEditAction;
    use Concerns\WithForceDeleteAction;
    use Concerns\WithRemoveAction;
    use Concerns\WithRestoreAction;
    use Concerns\WithUploadAction;
    use ValidatesRequests;

    /**
     * Action performed by the editor.
     */
    protected ?string $action = null;

    /**
     * Allowed dataTables editor actions.
     *
     * @var string[]
     */
    protected array $actions = [
        'create',
        'edit',
        'remove',
        'upload',
        'forceDelete',
        'restore',
    ];

    /**
     * List of custom editor actions.
     *
     * @var string[]
     */
    protected array $customActions = [];

    /**
     * @var null|class-string<TModel>|TModel
     */
    protected $model = null;

    /**
     * Indicates if all mass assignment is enabled on model.
     */
    protected bool $unguarded = false;

    /**
     * Upload directory relative to storage path.
     */
    protected string $uploadDir = 'editor';

    /**
     * Flag to force delete a model.
     */
    protected bool $forceDeleting = false;

    /**
     * Flag to restore a model from deleted state.
     */
    protected bool $restoring = false;

    /**
     * Filesystem disk config to use for upload.
     */
    protected string $disk = 'public';

    /**
     * Current request data that is being processed.
     */
    protected array $currentData = [];

    /**
     * Process dataTables editor action request.
     *
     * @return JsonResponse
     *
     * @throws DataTablesEditorException
     */
    public function process(Request $request): mixed
    {
        if ($request->get('action') && is_string($request->get('action'))) {
            $this->action = $request->get('action');
        } else {
            throw new DataTablesEditorException('Invalid action requested!');
        }

        if (! in_array($this->action, array_merge($this->actions, $this->customActions))) {
            throw new DataTablesEditorException(sprintf('Requested action (%s) not supported!', $this->action));
        }

        try {
            return $this->{$this->action}($request);
        } catch (Exception $exception) {
            $error = config('app.debug')
                ? '<strong>Server Error:</strong> '.$exception->getMessage()
                : $this->getUseFriendlyErrorMessage();

            app('log')->error($exception);

            return $this->toJson([], [], $error);
        }
    }

    protected function getUseFriendlyErrorMessage(): string
    {
        return 'An error occurs while processing your request.';
    }

    /**
     * Display success data in dataTables editor format.
     */
    protected function toJson(array $data, array $errors = [], string|array $error = ''): JsonResponse
    {
        $code = 200;

        $response = [
            'action' => $this->action,
            'data' => $data,
        ];

        if ($error) {
            $code = 400;
            $response['error'] = '<div class="DTE_Form_Error_Item">'.implode('</div><br class="DTE_Form_Error_Separator" /><div>', (array) $error).'</div>';
        }

        if ($errors) {
            $code = 422;
            $response['fieldErrors'] = $errors;
        }

        return new JsonResponse($response, $code);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get dataTables model.
     */
    public function getModel(): Model|string|null
    {
        return $this->model;
    }

    /**
     * Set the dataTables model on runtime.
     *
     * @param  class-string<TModel>|TModel  $model
     */
    public function setModel(Model|string $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get validation messages.
     */
    protected function messages(): array
    {
        return [];
    }

    protected function formatErrors(Validator $validator): array
    {
        $errors = [];

        collect($validator->errors())->each(function ($error, $key) use (&$errors) {
            $errors[] = [
                'name' => $key,
                'status' => $error[0],
            ];
        });

        return $errors;
    }

    /**
     * Get eloquent builder of the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder<TModel>
     */
    protected function getBuilder(): Builder
    {
        $model = $this->resolveModel();

        if (in_array(SoftDeletes::class, class_uses($model))) {
            // @phpstan-ignore-next-line
            return $model->newQuery()->withTrashed();
        }

        return $model->newQuery();
    }

    /**
     * Resolve model to used.
     *
     * @return TModel
     */
    protected function resolveModel(): Model
    {
        if (! $this->model instanceof Model) {
            $this->model = new $this->model;
        }

        $this->model->unguard($this->unguarded);

        return $this->model;
    }

    /**
     * Set model unguard state.
     *
     * @return $this
     */
    public function unguard(bool $state = true): static
    {
        $this->unguarded = $state;

        return $this;
    }

    protected function dataFromRequest(Request $request): array
    {
        return (array) $request->get('data');
    }

    public function saving(Model $model, array $data): array
    {
        return $data;
    }

    public function saved(Model $model, array $data): Model
    {
        return $model;
    }
}
