<?php

namespace Yajra\DataTables;

use Illuminate\Support\ServiceProvider;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;
use Yajra\DataTables\Commands\TransformerMakeCommand;
use Yajra\DataTables\Transformers\FractalTransformer;

class FractalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/datatables-fractal.php', 'datatables-fractal');
        $this->publishAssets();

        $this->registerMacro();
    }

    /**
     * Publish datatables assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes(
            [
                __DIR__.'/../config/datatables-fractal.php' => config_path('datatables-fractal.php'),
            ], 'datatables-fractal'
        );
    }

    /**
     * Register DataTables macro methods.
     */
    protected function registerMacro(): void
    {
        DataTableAbstract::macro('setTransformer', function ($transformer) {
            $this->transformer = [$transformer];

            return $this;
        });

        DataTableAbstract::macro('addTransformer', function ($transformer) {
            $this->transformer[] = $transformer;

            return $this;
        });

        DataTableAbstract::macro('setSerializer', function ($serializer) {
            $this->serializer = $serializer;

            return $this;
        });
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton('datatables.fractal', function () {
            $fractal = new Manager;
            $config = $this->app['config'];
            $request = $this->app['request'];

            $includesKey = $config->get('datatables-fractal.includes', 'include');
            if ($request->get($includesKey)) {
                $fractal->parseIncludes($request->get($includesKey));
            }

            $serializer = $config->get('datatables-fractal.serializer', DataArraySerializer::class);
            $fractal->setSerializer(new $serializer);

            return $fractal;
        });

        $this->app->singleton('datatables.transformer', function () {
            return new FractalTransformer($this->app->make('datatables.fractal'));
        });

        $this->commands([
            TransformerMakeCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'datatables.fractal',
            'datatables.transformer',
        ];
    }
}
