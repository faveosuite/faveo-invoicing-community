<?php

namespace Yajra\DataTables\Generators;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class DataTablesEditorCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datatables:editor
                            {name : The name of the dataTable editor.}
                            {--model : The name given will be used as the model is singular form.}
                            {--model-namespace= : The namespace of the model to be used.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new DataTables Editor class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'DataTableEditor';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceModelImport($stub)->replaceModel($stub);
    }

    /**
     * Replace model name.
     */
    protected function replaceModel(string &$stub): string
    {
        $model = explode('\\', $this->getModel());
        $model = array_pop($model);
        $stub = str_replace('ModelName', $model, $stub);

        return $stub;
    }

    /**
     * Get model name to use.
     */
    protected function getModel(): string
    {
        $name = $this->getNameInput();
        $rootNamespace = $this->laravel->getNamespace();
        $model = $this->option('model') || $this->option('model-namespace');
        $modelNamespace = $this->option('model-namespace') ?: config('datatables-buttons.namespace.model');

        return $model
            ? ($modelNamespace ?? $rootNamespace).'\\'.Str::singular($name)
            : $rootNamespace.'\\Models\\User';
    }

    /**
     * Replace model import.
     */
    protected function replaceModelImport(string &$stub): DataTablesEditorCommand
    {
        $stub = str_replace(
            'DummyModel', str_replace('\\\\', '\\', $this->getModel()), $stub
        );

        return $this;
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        $path = config('datatables-buttons.stub');
        if ($path && is_string($path)) {
            return base_path($path).'/editor.stub';
        }

        return __DIR__.'/stubs/editor.stub';
    }

    /**
     * Replace the filename.
     */
    protected function replaceFilename(string &$stub): string
    {
        $stub = str_replace(
            'DummyFilename', Str::slug($this->getNameInput()), $stub
        );

        return $stub;
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param  string  $name
     */
    protected function qualifyClass($name): string
    {
        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        if (! Str::contains(Str::lower($name), 'datatable')) {
            $name .= $this->type;
        }

        return $this->getDefaultNamespace(trim((string) $rootNamespace, '\\')).'\\'.$name;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\'.config('datatables-buttons.namespace.base', 'DataTables');
    }
}
