<?php

namespace App\Plugins;

abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($module = $this->getModule(func_get_args())) {
            $this->publishes([
                'app/plugins/'.$module.'/Config/config.php' => config_path($module.'/config.php'),
            ]);
        }
    }

    public function register()
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

    public function getModule($args)
    {
        $module = (isset($args[0]) and is_string($args[0])) ? $args[0] : null;

        return $module;
    }
}
