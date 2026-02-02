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
    /** @use \Yajra\DataTables\Concerns\WithCreateAction<TModel> */
    use Concerns\WithCreateAction;

    /** @use \Yajra\DataTables\Concerns\WithEditAction<TModel> */
    use Concerns\WithEditAction;

    use Concerns\WithForceDeleteAction;
    use Concerns\WithReadAction;

    /** @use \Yajra\DataTables\Concerns\WithRemoveAction<TModel> */
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
        'read',
    ];

    /**
     * List of custom editor actions.
     *
     * @var array<array-key, string|class-string>
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

    public function __invoke(): JsonResponse
    {
        return $this->process();
    }

    /**
     * Process dataTables editor action request.
     */
    public function process(?Request $request = null): JsonResponse
    {
        $request ??= request();
        $this->action = $request->get('action');

        throw_unless(
            $this->isValidAction($request),
            DataTablesEditorException::class,
            'Invalid action requested!'
        );

        try {
            if (method_exists($this, $this->action)) {
                return $this->{$this->action}($request);
            }

            // @phpstan-ignore-next-line  method.nonObject
            return resolve($this->customActions[$this->action], ['editor' => $this])->handle($request);
        } catch (Exception $exception) {
            $error = config('app.debug')
                ? '<strong>Server Error:</strong> '.$exception->getMessage()
                : $this->getUseFriendlyErrorMessage();

            app('log')->error($exception);

            return $this->toJson([], [], $error);
        }
    }

    public function isValidAction(Request $request): bool
    {
        $validActions = $this->actions;
        foreach ($this->customActions as $key => $action) {
            $validActions[] = is_numeric($key) ? $action : $key;
        }

        return in_array($this->action, $validActions)
            && $request->get('action')
            && is_string($request->get('action'));
    }

    public function getUseFriendlyErrorMessage(): string
    {
        return 'An error occurs while processing your request.';
    }

    /**
     * Display success data in dataTables editor format.
     */
    public function toJson(array $data, array $errors = [], string|array $error = ''): JsonResponse
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
     *
     * @return class-string<TModel>|TModel|null
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

    public function formatErrors(Validator $validator): array
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
        if (is_null($this->model)) {
            throw new DataTablesEditorException('Model not set.');
        }

        if (is_string($this->model)) {
            $this->model = new $this->model;
        }

        $this->model->unguard($this->unguarded);

        return $this->model;
    }

    /**
     * Set model unguarded state.
     */
    public function unguard(bool $state = true): static
    {
        $this->unguarded = $state;

        return $this;
    }

    public function dataFromRequest(Request $request): array
    {
        return (array) $request->get('data');
    }

    /**
     * @param  TModel  $model
     */
    public function saving(Model $model, array $data): array
    {
        return $data;
    }

    /**
     * @param  TModel  $model
     * @return TModel
     */
    public function saved(Model $model, array $data): Model
    {
        return $model;
    }
}
