<?php

namespace Yajra\DataTables\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class TransformerMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:transformer
                            {name : The name of the class}
                            {include? : Name of the class to include.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Transformer Class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Transformer';

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub  Contents of the stub
     * @param  string  $name  The class name
     */
    protected function replaceClass($stub, $name): string
    {
        $stub = parent::replaceClass($stub, $name.'Transformer');
        $stub = str_replace('Dummy', ucfirst($this->argument('name')), $stub);
        $stub = str_replace('dummy', lcfirst($this->argument('name')), $stub);

        if ($this->argument('include')) {
            $stub = str_replace('Item', ucfirst($this->argument('include')), $stub);
            $stub = str_replace('item', lcfirst($this->argument('include')), $stub);
        }

        return $stub;
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->argument('include') ?
            __DIR__.'/stubs/transformer.inc.stub' :
            __DIR__.'/stubs/transformer.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace  The root namespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Transformers';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name  Name of the class with namespace
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'Transformer.php';
    }
}
