<?php

declare(strict_types=1);

namespace App\Plugins;

use Override;

abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        if ($module = $this->getModule(func_get_args())) {
            $this->publishes([
                'app/plugins/'.$module.'/Config/config.php' => config_path($module.'/config.php'),
            ]);
        }
    }

    #[Override]
    public function register(): void
    {
        if ($module = $this->getModule(func_get_args())) {
            $this->publishes([
                'app/plugins/'.$module.'/Config/config.php' => config_path($module.'/config.php'),
            ]);

            // Add routes
            $routes = app_path().'/Plugins/'.$module.'/routes.php';
            if (file_exists($routes)) {
                require $routes;
            }
        }
    }

    public function getModule(mixed $args): mixed
    {
        return (isset($args[0]) && is_string($args[0])) ? $args[0] : null;
    }
}
