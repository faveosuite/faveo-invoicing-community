<?php

namespace Devio\Pipedrive;

use Devio\Pipedrive\Exceptions\PipedriveException;
use Illuminate\Support\ServiceProvider;

class PipedriveServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booting( function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias( 'Pipedrive', 'Devio\Pipedrive\PipedriveFacade' );
        } );

        $this->app->singleton(Pipedrive::class, function ($app) {
            $token = $app['config']->get('services.pipedrive.token');
            $uri = $app['config']->get('services.pipedrive.uri') ?: 'https://api.pipedrive.com/v1/';
            $guzzleVersion = $app['config']->get('services.pipedrive.guzzle_version') ?: 6;

            if (! $token) {
                throw new PipedriveException('Pipedrive was not configured in services.php configuration file.');
            }

            return new Pipedrive($token, $uri, $guzzleVersion);
        });

        $this->app->alias(Pipedrive::class, 'pipedrive');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['pipedrive'];
    }
}
