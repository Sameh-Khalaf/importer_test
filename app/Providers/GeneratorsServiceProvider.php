<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Way\Generators\GeneratorsServiceProvider as BaseProvider;

class GeneratorsServiceProvider extends BaseProvider
{
    /**
     * Register the config paths
     */
    public function registerConfig()
    {
        $userConfigFile    = config_path().'/generators.config.php';
        $packageConfigFile = base_path().'/vendor/xethron/laravel-4-generators/src/config/config.php';
        $config            = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config     = array_replace_recursive($config, $userConfig);
        }

        $this->app['config']->set('generators.config', $config);
    }
}

